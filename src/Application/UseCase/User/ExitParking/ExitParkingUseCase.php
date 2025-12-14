<?php

namespace App\Application\UseCase\User\ExitParking;

use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Entity\ParkingSession;
use DateTimeImmutable;
use RuntimeException;

class ExitParkingUseCase
{
    private ParkingSessionRepositoryInterface $sessionRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private \App\Domain\Repository\InvoiceRepositoryInterface $invoiceRepository;

    public function __construct(
        ParkingSessionRepositoryInterface $sessionRepository,
        ReservationRepositoryInterface $reservationRepository,
        \App\Domain\Repository\InvoiceRepositoryInterface $invoiceRepository
    ) {
        $this->sessionRepository = $sessionRepository;
        $this->reservationRepository = $reservationRepository;
        $this->invoiceRepository = $invoiceRepository;
    }

    public function execute(ExitParkingRequest $request): ParkingSession
    {
        // 1. Find active session
        $session = $this->sessionRepository->findActiveSessionByUserId($request->userId);
        if (!$session) {
            throw new RuntimeException("Aucun stationnement actif trouvé.");
        }
        if ($session->getParkingId() !== $request->parkingId) {
            throw new RuntimeException("Vous n'êtes pas stationné dans ce parking.");
        }

        // 2. Set Exit Time
        $exitTime = new DateTimeImmutable();
        $session->setExitDateTime($exitTime);

        // 3. Calculate Final Amount / Penalty
        $finalAmount = 0.0;
        $isPenalty = false;
        $reservationId = $session->getReservationId();

        if ($reservationId) {
            $reservation = $this->reservationRepository->findById($reservationId);
            if ($reservation) {
                // Base price already paid/calculated in reservation.
                // We add penalty if exit > reservation_end
                if ($exitTime > $reservation->getEndDateTime()) {
                    // OVERSTAY PENALTY
                    // Logic: 20 EUR fixed penalty for now as per requirements
                    $penalty = 20.0;
                    // Add actual duration cost? Or just penalty? 
                    // Simplifying: Just adding penalty to the "final amount" tracked on session. 
                    // Ideally, finalAmount on session = Total Cost of Session.
                    // If reservation was 10 EUR, and penalty 20, is final amount 30? Or just the extra 20 to pay?
                    // Let's assume finalAmount on session is the TOTAL value of the service.

                    $paidAmount = $reservation->getCalculatedAmount() ?? 0.0;
                    $finalAmount = $paidAmount + $penalty;
                    $isPenalty = true;
                } else {
                    $finalAmount = $reservation->getCalculatedAmount();
                }
            }
        } else {
            // Subscription: Price is 0 (covered by sub)
            // Unless out of bounds check (future)
            $finalAmount = 0.0;
        }

        $session->setFinalAmount($finalAmount);
        $session->setPenaltyApplied($isPenalty);

        $savedSession = $this->sessionRepository->save($session);

        // 4. Generate Invoice (if applicable, e.g. amount > 0 or always?)
        // Let's generate invoice for record keeping even if amount is 0 (subscription)
        // Or only for reservations.
        // Requirement implies Invoices for completed sessions.

        $details = [
            'penalty_applied' => $isPenalty,
            'duration_minutes' => ($exitTime->getTimestamp() - $session->getEntryDateTime()->getTimestamp()) / 60
        ];

        // Assuming ID required, passing 0/random.
        // Ideally should use UUID or auto-inc.
        // Assuming Repo handles 0 for insertion (if auto-inc).

        $invoice = new \App\Domain\Entity\Invoice(
            0,
            $reservationId,
            $savedSession->getSessionId(),
            new DateTimeImmutable(),
            $finalAmount / 1.20, // HT approx
            $finalAmount,
            json_encode($details),
            $reservationId ? 'reservation' : 'session'
        );

        $this->invoiceRepository->save($invoice);

        return $savedSession;
    }
}

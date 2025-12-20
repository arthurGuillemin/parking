<?php

namespace App\Application\UseCase\User\ExitParking;

use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Repository\InvoiceRepositoryInterface;
use App\Domain\Entity\ParkingSession;
use App\Domain\Entity\Invoice;
use App\Application\DTO\Response\ParkingSessionResponse;
use DateTimeImmutable;
use RuntimeException;

class ExitParkingUseCase
{
    private ParkingSessionRepositoryInterface $sessionRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private InvoiceRepositoryInterface $invoiceRepository;

    public function __construct(
        ParkingSessionRepositoryInterface $sessionRepository,
        ReservationRepositoryInterface $reservationRepository,
        InvoiceRepositoryInterface $invoiceRepository
    ) {
        $this->sessionRepository = $sessionRepository;
        $this->reservationRepository = $reservationRepository;
        $this->invoiceRepository = $invoiceRepository;
    }

    public function execute(ExitParkingRequest $request): ParkingSessionResponse
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
                if ($exitTime > $reservation->getEndDateTime()) {
                    // OVERSTAY PENALTY - 20€ fixed
                    $penalty = 20.0;
                    $paidAmount = $reservation->getCalculatedAmount() ?? 0.0;
                    $finalAmount = $paidAmount + $penalty;
                    $isPenalty = true;
                } else {
                    $finalAmount = $reservation->getCalculatedAmount();
                }
            }
        } else {
            // Subscription: Price is 0 (covered by sub)
            $finalAmount = 0.0;
        }

        $session->setFinalAmount($finalAmount);
        $session->setPenaltyApplied($isPenalty);

        $savedSession = $this->sessionRepository->save($session);

        // 4. Generate Invoice
        $details = [
            'penalty_applied' => $isPenalty,
            'duration_minutes' => ($exitTime->getTimestamp() - $session->getEntryDateTime()->getTimestamp()) / 60
        ];

        $invoice = new Invoice(
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

        return new ParkingSessionResponse($savedSession);
    }
}

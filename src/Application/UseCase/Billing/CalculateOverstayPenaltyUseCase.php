<?php

namespace App\Application\UseCase\Billing;

use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Service\PricingService;

class CalculateOverstayPenaltyUseCase
{
    private ReservationRepositoryInterface $reservationRepository;
    private ParkingSessionRepositoryInterface $parkingSessionRepository;
    private PricingService $pricingService;

    public function __construct(
        ReservationRepositoryInterface $reservationRepository,
        ParkingSessionRepositoryInterface $parkingSessionRepository,
        PricingService $pricingService
    ) {
        $this->reservationRepository = $reservationRepository;
        $this->parkingSessionRepository = $parkingSessionRepository;
        $this->pricingService = $pricingService;
    }

    public function execute(int $sessionId, \DateTimeImmutable $exitTime): float
    {
        // 1. Fetch Session
        $session = $this->parkingSessionRepository->findById($sessionId);
        if (!$session) {
            throw new \RuntimeException("Session not found");
        }

        // 2. Fetch Reservation
        // If no reservation, this penalty logic might not apply (e.g. pure subscription or ad-hoc).
        // The prompt says "Un conducteur qui possède une réservation...".
        // If no reservation ID, maybe standard billing applies without "penalty" or different logic.
        $reservationId = $session->getReservationId();
        if (!$reservationId) {
            // Logic for non-reservation parking (e.g. ad-hoc) - just calculate price?
            // User prompt specifically talks about "penalty if overstay reservation".
            // Return 0 or standard price? Let's just calculate standard price for actual duration.
            $duration = $exitTime->diff($session->getEntryDateTime());
            return $this->pricingService->calculatePrice($session->getParkingId(), $duration);
        }

        $reservation = $this->reservationRepository->findById($reservationId);
        if (!$reservation) {
            throw new \RuntimeException("Reservation not found");
        }

        // 3. Compare Durations / End Time
        // "reste au-delà d'un créneau d'une réservation"
        // If ExitTime > ReservationEndTime

        $reservationEnd = $reservation->getEndDateTime();

        if ($exitTime > $reservationEnd) {
            // PENALTY CASE
            // "doit payer comme une réservation de 4h + 20€"
            // i.e. Price for total actual duration + 20.

            $actualDuration = $exitTime->diff($session->getEntryDateTime());
            $basePrice = $this->pricingService->calculatePrice($session->getParkingId(), $actualDuration);

            return $basePrice + 20.0;
        }

        // NO PENALTY CASE
        // Simply return standard price for the RESERVED duration? 
        // Or assumes price is already paid?
        // Usually, if you leave on time, you paid your reservation price.
        // If we are calculating "What is the final cost", it should match reservation price.
        // Let's assume "Price for Reserved Duration".

        $reservedDuration = $reservationEnd->diff($reservation->getStartDateTime());
        return $this->pricingService->calculatePrice($session->getParkingId(), $reservedDuration);
    }
}

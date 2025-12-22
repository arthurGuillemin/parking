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
        $session = $this->parkingSessionRepository->findById($sessionId);
        if (!$session) {
            throw new \RuntimeException("Session not found");
        }

        $reservationId = $session->getReservationId();
        if (!$reservationId) {
            $duration = $exitTime->diff($session->getEntryDateTime());
            return $this->pricingService->calculatePrice($session->getParkingId(), $duration, $exitTime);
        }

        $reservation = $this->reservationRepository->findById($reservationId);
        if (!$reservation) {
            throw new \RuntimeException("Reservation not found");
        }

        $reservationEnd = $reservation->getEndDateTime();

        if ($exitTime > $reservationEnd) {

            $actualDuration = $exitTime->diff($session->getEntryDateTime());
            $basePrice = $this->pricingService->calculatePrice($session->getParkingId(), $actualDuration, $exitTime);

            return $basePrice + 20.0;
        }

        $reservedDuration = $reservationEnd->diff($reservation->getStartDateTime());
        return $this->pricingService->calculatePrice($session->getParkingId(), $reservedDuration, $exitTime);
    }
}

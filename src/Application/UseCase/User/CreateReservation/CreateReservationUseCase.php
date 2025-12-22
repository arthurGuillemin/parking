<?php

namespace App\Application\UseCase\User\CreateReservation;

use App\Domain\Entity\Reservation;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Service\ParkingAvailabilityService;
use App\Domain\Service\PricingService;
use App\Application\UseCase\Owner\GetAvailableSpots\GetAvailableSpotsRequest;

class CreateReservationUseCase
{
    private ReservationRepositoryInterface $reservationRepository;
    private ParkingAvailabilityService $parkingAvailabilityService;
    private PricingService $pricingService;

    public function __construct(
        ReservationRepositoryInterface $reservationRepository,
        ParkingAvailabilityService $parkingAvailabilityService,
        PricingService $pricingService
    ) {
        $this->reservationRepository = $reservationRepository;
        $this->parkingAvailabilityService = $parkingAvailabilityService;
        $this->pricingService = $pricingService;
    }

    public function execute(int $parkingId, string $userId, \DateTimeImmutable $start, \DateTimeImmutable $end): Reservation
    {
        // 1. Validation: End > Start
        if ($end <= $start) {
            throw new \InvalidArgumentException("La date de fin doit être après la date de début.");
        }

        // 2. Vérifier si le parking est disponible
        $request = new GetAvailableSpotsRequest($parkingId, $start); 
        $spots = $this->parkingAvailabilityService->getAvailableSpots($request);

        if ($spots <= 0) {
            throw new \RuntimeException("Aucune place disponible pour ce créneau.");
        }

        // 3. Calculer le prix
        $duration = $start->diff($end);
        $amount = $this->pricingService->calculatePrice($parkingId, $duration, $start);

        // 4. Créer l'entité
        $reservation = new Reservation(
            0, // ID 0 means new
            $userId,
            $parkingId,
            $start,
            $end,
            'confirmed', // Use valid ENUM status
            $amount,
            null // Final amount null initially
        );

        // 5. Sauvegarder
        return $this->reservationRepository->save($reservation);
    }
}

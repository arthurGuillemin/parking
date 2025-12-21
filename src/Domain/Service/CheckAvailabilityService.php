<?php

namespace App\Domain\Service;

use App\Domain\Entity\Parking;
use App\Domain\Repository\ReservationRepositoryInterface;

class CheckAvailabilityService
{
    private ReservationRepositoryInterface $reservationRepository;

    public function __construct(ReservationRepositoryInterface $reservationRepository)
    {
        $this->reservationRepository = $reservationRepository;
    }

    public function checkAvailability(Parking $parking, \DateTimeImmutable $start, \DateTimeImmutable $end): bool
    {
        // récupérer la capacité du parking
        $capacity = $parking->getTotalCapacity();

        // Count ACTIVE overstayers (people who should have left but are still here)
        // They occupy a spot NOW and reduce the *effective* capacity of the parking.
        $overstayersCount = $this->reservationRepository->countActiveOverstayers(
            $parking->getParkingId(),
            new \DateTimeImmutable() // Check relative to NOW
        );

        $effectiveCapacity = $capacity - $overstayersCount;
        if ($effectiveCapacity < 0)
            $effectiveCapacity = 0;

        // compter les réservations qui se chevauchent
        $overlappingCount = $this->reservationRepository->countOverlapping(
            $parking->getParkingId(),
            $start,
            $end
        );

        return $overlappingCount < $effectiveCapacity;
    }
}

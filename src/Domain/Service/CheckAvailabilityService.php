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

        // compte les personnes qui ont dépassé leur heure de sortie
        $overstayersCount = $this->reservationRepository->countActiveOverstayers(
            $parking->getParkingId(),
            new \DateTimeImmutable()
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

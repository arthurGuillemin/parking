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
        // 1. Get Total Capacity
        $capacity = $parking->getTotalCapacity();

        // 2. Count overlapping reservations
        // This count assumes that any reservation overlapping the interval [start, end] consumes 1 spot at some point.
        // A more granular check would be checking max concurrent reservations at any minute,
        // but typically "overlapping" covers the worst case if standard simplistic query is used.
        // If the query is "count distinct reservations that overlap", it implies that if we have 10 spots, and 10 res overlap, we are full.
        $overlappingCount = $this->reservationRepository->countOverlapping(
            $parking->getParkingId(),
            $start,
            $end
        );

        return $overlappingCount < $capacity;
    }
}

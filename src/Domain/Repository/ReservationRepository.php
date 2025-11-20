<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Reservation;

interface ReservationRepository
{
    public function findById(int $id): ?Reservation;

    public function findByUserId(string $userId): array;

    public function findForParkingBetween(
        int $parkingId,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end
    ): array;

    public function save(Reservation $reservation): Reservation;
}

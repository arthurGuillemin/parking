<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Reservation;

interface ReservationRepositoryInterface
{
    public function findById(int $id): ?Reservation;

    public function findByUserId(string $userId): array;

    public function findForParkingBetween(
        int $parkingId,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end
    ): array;

    public function save(Reservation $reservation): Reservation;

    public function findAllByParkingId(int $parkingId);

    public function countOverlapping(int $parkingId, \DateTimeImmutable $start, \DateTimeImmutable $end): int;

    public function findActiveReservation(string $userId, int $parkingId, \DateTimeImmutable $atTime): ?Reservation;
}

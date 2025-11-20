<?php

namespace App\Domain\Repository;

use App\Domain\Entity\ParkingSession;

interface ParkingSessionRepositoryInterface {
    public function findById(int $id): ?ParkingSession;

    public function findActiveSessionByUserId(string $userId): ?ParkingSession;

    public function findByReservationId(int $reservationId): ?ParkingSession;

    public function save(ParkingSession $session): ParkingSession;
}

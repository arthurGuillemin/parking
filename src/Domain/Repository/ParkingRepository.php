<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Parking;

interface ParkingRepository
{
    public function findById(int $id): ?Parking;
    public function findAll(): array;
    public function findByOwnerId(string $ownerId): array;
    public function save(Parking $parking): Parking;
}

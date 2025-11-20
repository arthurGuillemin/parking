<?php

namespace App\Domain\Repository;

use App\Domain\Entity\OpeningHour;

interface OpeningHourRepository
{
    public function findById(int $id): ?OpeningHour;

    public function findByParkingId(int $parkingId): array;

    public function save(OpeningHour $hour): OpeningHour;
}

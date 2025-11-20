<?php

namespace App\Domain\Repository;

use App\Domain\Entity\OpeningHour;

interface OpeningHourRepositoryInterface {
    public function findById(int $id): ?OpeningHour;

    public function findByParkingId(int $parkingId): array;

    public function save(OpeningHour $hour): OpeningHour;
}

<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Subscription;

interface SubscriptionRepositoryInterface {
    public function findById(int $id): ?Subscription;

    public function findByUserId(string $userId): array;

    public function findActiveByUserAndParking(
        string $userId,
        int $parkingId,
        \DateTimeImmutable $date
    ): array;

    public function save(Subscription $subscription): Subscription;
    public function findByParkingIdAndMonth(int $parkingId, int $year, int $month): array;
}

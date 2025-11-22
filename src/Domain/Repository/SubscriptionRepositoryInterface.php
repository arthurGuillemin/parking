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

    /**
     * Retourne tous les abonnements d'un parking pour un mois donné.
     * @param int $parkingId
     * @param int $year
     * @param int $month
     * @return Subscription[]
     */
    public function findByParkingIdAndMonth(int $parkingId, int $year, int $month): array;
}

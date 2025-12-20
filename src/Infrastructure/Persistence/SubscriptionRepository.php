<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Subscription;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use PDO;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?Subscription
    {
        $stmt = $this->pdo->prepare('SELECT * FROM subscriptions WHERE id = ?');
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->mapToEntity($data) : null;
    }

    public function findByUserId(string $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM subscriptions WHERE user_id = ?');
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => $this->mapToEntity($row), $rows);
    }

    public function findActiveByUserId(string $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM subscriptions WHERE user_id = ? AND status = "active"');
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => $this->mapToEntity($row), $rows);
    }

    public function findActiveByUserAndParking(
        string $userId,
        int $parkingId,
        \DateTimeImmutable $date
    ): array {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM subscriptions 
             WHERE user_id = ? 
             AND parking_id = ? 
             AND start_date <= ? 
             AND (end_date IS NULL OR end_date >= ?) 
             AND status = "active"'
        );
        $stmt->execute([$userId, $parkingId, $date->format('Y-m-d'), $date->format('Y-m-d')]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => $this->mapToEntity($row), $rows);
    }

    public function save(Subscription $subscription): Subscription
    {
        if ($subscription->getSubscriptionId() === 0) {
            return $this->insert($subscription);
        } else {
            return $this->update($subscription);
        }
    }

    public function findByParkingIdAndMonth(int $parkingId, int $year, int $month): array
    {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $stmt = $this->pdo->prepare(
            'SELECT * FROM subscriptions 
             WHERE parking_id = ? 
             AND start_date <= ? 
             AND (end_date IS NULL OR end_date >= ?) 
             AND status = "active"'
        );
        $stmt->execute([$parkingId, $endDate, $startDate]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => $this->mapToEntity($row), $rows);
    }

    private function insert(Subscription $subscription): Subscription
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO subscriptions 
             (user_id, parking_id, type_id, start_date, end_date, status, monthly_price) 
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $subscription->getUserId(),
            $subscription->getParkingId(),
            $subscription->getTypeId(),
            $subscription->getStartDate()->format('Y-m-d H:i:s'),
            $subscription->getEndDate()?->format('Y-m-d H:i:s'),
            $subscription->getStatus(),
            $subscription->getMonthlyPrice(),
        ]);

        // Récupérer l'ID généré
        $id = (int) $this->pdo->lastInsertId();

        return new Subscription(
            $id,
            $subscription->getUserId(),
            $subscription->getParkingId(),
            $subscription->getTypeId(),
            $subscription->getStartDate(),
            $subscription->getEndDate(),
            $subscription->getStatus(),
            $subscription->getMonthlyPrice()
        );
    }

    private function update(Subscription $subscription): Subscription
    {
        $stmt = $this->pdo->prepare(
            'UPDATE subscriptions 
             SET user_id = ?, parking_id = ?, type_id = ?, start_date = ?, 
                 end_date = ?, status = ?, monthly_price = ? 
             WHERE id = ?'
        );

        $stmt->execute([
            $subscription->getUserId(),
            $subscription->getParkingId(),
            $subscription->getTypeId(),
            $subscription->getStartDate()->format('Y-m-d H:i:s'),
            $subscription->getEndDate()?->format('Y-m-d H:i:s'),
            $subscription->getStatus(),
            $subscription->getMonthlyPrice(),
            $subscription->getSubscriptionId(),
        ]);

        return $subscription;
    }

    private function mapToEntity(array $data): Subscription
    {
        return new Subscription(
            (int) $data['id'],
            $data['user_id'],
            (int) $data['parking_id'],
            $data['type_id'] ? (int) $data['type_id'] : null,
            new \DateTimeImmutable($data['start_date']),
            $data['end_date'] ? new \DateTimeImmutable($data['end_date']) : null,
            $data['status'],
            (float) $data['monthly_price']
        );
    }
}
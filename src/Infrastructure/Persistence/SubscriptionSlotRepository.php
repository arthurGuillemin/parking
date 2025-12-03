<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\SubscriptionSlot;
use App\Domain\Repository\SubscriptionSlotRepositoryInterface;
use PDO;

class SubscriptionSlotRepository implements SubscriptionSlotRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?SubscriptionSlot
    {
        $stmt = $this->pdo->prepare('SELECT * FROM subscription_slots WHERE id = ?');
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->mapToEntity($data) : null;
    }

    public function findBySubscriptionTypeId(int $typeId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM subscription_slots WHERE subscription_type_id = ?');
        $stmt->execute([$typeId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => $this->mapToEntity($row), $rows);
    }

    public function save(SubscriptionSlot $slot): SubscriptionSlot
    {
        if ($slot->getSubscriptionSlotId() === 0) {
            return $this->insert($slot);
        } else {
            return $this->update($slot);
        }
    }

    private function insert(SubscriptionSlot $slot): SubscriptionSlot
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO subscription_slots 
             (subscription_type_id, weekday, start_time, end_time) 
             VALUES (?, ?, ?, ?)'
        );

        $stmt->execute([
            $slot->getSubscriptionTypeId(),
            $slot->getWeekday(),
            $slot->getStartTime()->format('H:i:s'),
            $slot->getEndTime()->format('H:i:s'),
        ]);

        $id = (int)$this->pdo->lastInsertId();

        return new SubscriptionSlot(
            $id,
            $slot->getSubscriptionTypeId(),
            $slot->getWeekday(),
            $slot->getStartTime(),
            $slot->getEndTime()
        );
    }

    private function update(SubscriptionSlot $slot): SubscriptionSlot
    {
        $stmt = $this->pdo->prepare(
            'UPDATE subscription_slots 
             SET subscription_type_id = ?, weekday = ?, start_time = ?, end_time = ? 
             WHERE id = ?'
        );

        $stmt->execute([
            $slot->getSubscriptionTypeId(),
            $slot->getWeekday(),
            $slot->getStartTime()->format('H:i:s'),
            $slot->getEndTime()->format('H:i:s'),
            $slot->getSubscriptionSlotId(),
        ]);

        return $slot;
    }

    private function mapToEntity(array $data): SubscriptionSlot
    {
        return new SubscriptionSlot(
            (int)$data['id'],
            (int)$data['subscription_type_id'],
            (int)$data['weekday'],
            new \DateTimeImmutable($data['start_time']),
            new \DateTimeImmutable($data['end_time'])
        );
    }
}
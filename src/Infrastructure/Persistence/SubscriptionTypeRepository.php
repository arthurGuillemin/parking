<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\SubscriptionType;
use App\Domain\Repository\SubscriptionTypeRepositoryInterface;
use PDO;

class SubscriptionTypeRepository implements SubscriptionTypeRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?SubscriptionType
    {
        $stmt = $this->pdo->prepare('SELECT * FROM subscription_types WHERE id = ?');
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->mapToEntity($data) : null;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM subscription_types');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => $this->mapToEntity($row), $rows);
    }

    public function save(SubscriptionType $type): SubscriptionType
    {
        if ($type->getSubscriptionTypeId() === 0) {
            return $this->insert($type);
        } else {
            return $this->update($type);
        }
    }

    private function insert(SubscriptionType $type): SubscriptionType
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO subscription_types (parking_id, name, description) 
             VALUES (?, ?, ?)'
        );

        $stmt->execute([
            $type->getParkingId(),
            $type->getName(),
            $type->getDescription(),
        ]);

        $id = (int)$this->pdo->lastInsertId();

        return new SubscriptionType(
            $id,
            $type->getParkingId(),
            $type->getName(),
            $type->getDescription()
        );
    }

    private function update(SubscriptionType $type): SubscriptionType
    {
        $stmt = $this->pdo->prepare(
            'UPDATE subscription_types 
             SET parking_id = ?, name = ?, description = ? 
             WHERE id = ?'
        );

        $stmt->execute([
            $type->getParkingId(),
            $type->getName(),
            $type->getDescription(),
            $type->getSubscriptionTypeId(),
        ]);

        return $type;
    }

    private function mapToEntity(array $data): SubscriptionType
    {
        return new SubscriptionType(
            (int)$data['id'],
            (int)$data['parking_id'],
            $data['name'],
            $data['description']
        );
    }
}
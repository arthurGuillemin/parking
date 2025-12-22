<?php

namespace App\Infrastructure\Persistence\File;

use App\Domain\Entity\SubscriptionType;
use App\Domain\Repository\SubscriptionTypeRepositoryInterface;
use App\Infrastructure\Storage\FileStorage;

class FileSubscriptionTypeRepository implements SubscriptionTypeRepositoryInterface
{
    private FileStorage $storage;

    public function __construct()
    {
        $this->storage = new FileStorage(__DIR__ . '/storage/subscription-types.json');
    }

    public function findById(int $id): ?SubscriptionType
    {
        foreach ($this->storage->read() as $row) {
            if ($row['id'] === $id) {
                return $this->mapToSubscriptionType($row);
            }
        }
        return null;
    }

    public function findAll(): array
    {
        return array_map(
            [$this, 'mapToSubscriptionType'],
            $this->storage->read()
        );
    }

    public function findByParkingId(int $parkingId): array
    {
        return [];
    }

    public function save(SubscriptionType $type): SubscriptionType
    {
        $data = $this->storage->read();
        $found = false;

        foreach ($data as &$row) {
            if ($row['id'] === $type->getSubscriptionTypeId()) {
                $row = $this->mapFromSubscriptionType($type);
                $found = true;
                break;
            }
        }

        if (!$found) {
            $data[] = $this->mapFromSubscriptionType($type);
        }

        $this->storage->write($data);

        return $type;
    }

    private function mapToSubscriptionType(array $row): SubscriptionType
    {
        return new SubscriptionType(
            id: (int) $row['id'],
            parkingId: 0,
            name: $row['name'],
            description: $row['description'],
            monthlyPrice: (float) $row['monthly_price']
        );
    }

    private function mapFromSubscriptionType(SubscriptionType $type): array
    {
        return [
            'id' => $type->getSubscriptionTypeId(),
            'name' => $type->getName(),
            'description' => $type->getDescription(),
            'monthly_price' => $type->getMonthlyPrice(),
        ];
    }
}

<?php

namespace App\Infrastructure\Persistence\File;

use App\Domain\Entity\SubscriptionSlot;
use App\Domain\Repository\SubscriptionSlotRepositoryInterface;
use App\Infrastructure\Storage\FileStorage;
use DateTimeImmutable;

class FileSubscriptionSlotRepository implements SubscriptionSlotRepositoryInterface
{
    private FileStorage $storage;

    public function __construct()
    {
        $this->storage = new FileStorage(__DIR__ . '/storage/subscription-slots.json');
    }

    public function findById(int $id): ?SubscriptionSlot
    {
        foreach ($this->storage->read() as $row) {
            if ($row['id'] === $id) {
                return $this->mapToSubscriptionSlot($row);
            }
        }
        return null;
    }

    public function findBySubscriptionTypeId(int $typeId): array
    {
        $results = [];

        foreach ($this->storage->read() as $row) {
            if ($row['subscriptionTypeId'] === $typeId) {
                $results[] = $this->mapToSubscriptionSlot($row);
            }
        }

        return $results;
    }

    public function save(SubscriptionSlot $slot): SubscriptionSlot
    {
        $data = $this->storage->read();
        $found = false;

        foreach ($data as &$row) {
            if ($row['id'] === $slot->getSubscriptionSlotId()) {
                $row = $this->mapFromSubscriptionSlot($slot);
                $found = true;
                break;
            }
        }

        if (!$found) {
            $data[] = $this->mapFromSubscriptionSlot($slot);
        }

        $this->storage->write($data);

        return $slot;
    }

    public function delete(int $id): void
    {
        $this->storage->write(
            array_values(
                array_filter(
                    $this->storage->read(),
                    fn ($row) => $row['id'] !== $id
                )
            )
        );
    }

    private function mapToSubscriptionSlot(array $row): SubscriptionSlot
    {
        return new SubscriptionSlot(
            id: (int) $row['id'],
            subscriptionId: (int) $row['subscription_id'], # a implementer
            weekdayStart: (int) $row['weekday_start'], # a implementer
            weekdayEnd: (int) $row['weekday_end'],  # a implementer
            startTime: new DateTimeImmutable($row['start_time']),
            endTime: new DateTimeImmutable($row['end_time'])
        );
    }

    private function mapFromSubscriptionSlot(SubscriptionSlot $slot): array
    {
        return [
            'id' => $slot->getSubscriptionSlotId(),
            'subscription_id' => $slot->getSubscriptionId(), # a implementer
            'weekday_start' => $slot->getWeekdayStart(), # a implementer
            'weekday_end' => $slot->getWeekdayEnd(),  # a implementer
            'start_time' => $slot->getStartTime()->format('H:i:s'),
            'end_time' => $slot->getEndTime()->format('H:i:s'),
        ];
    }
}

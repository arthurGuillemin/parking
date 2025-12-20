<?php

namespace App\Infrastructure\Persistence\File;

use App\Domain\Entity\Subscription;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Infrastructure\Storage\FileStorage;
use DateTimeImmutable;

class FileSubscriptionRepository implements SubscriptionRepositoryInterface
{
    private FileStorage $storage;

    public function __construct()
    {
        $this->storage = new FileStorage(__DIR__ . '/storage/subscriptions.json');
    }

    public function findById(int $id): ?Subscription
    {
        foreach ($this->storage->read() as $row) {
            if ($row['id'] === $id) {
                return $this->mapToSubscription($row);
            }
        }
        return null;
    }

    public function findByUserId(string $userId): array
    {
        return array_values(array_filter(
            array_map([$this, 'mapToSubscription'], $this->storage->read()),
            fn ($s) => $s->getUserId() === $userId
        ));
    }

    public function findActiveByUserId(string $userId): array
    {
        $today = new DateTimeImmutable();

        return array_filter(
            $this->findByUserId($userId),
            fn ($s) =>
                $s->getStatus() === 'active' &&
                $s->getStartDate() <= $today &&
                ($s->getEndDate() === null || $s->getEndDate() >= $today)
        );
    }

    public function findActiveByUserAndParking(string $userId, int $parkingId, DateTimeImmutable $date): array
    {
        return array_filter(
            $this->findByUserId($userId),
            fn ($s) =>
                $s->getParkingId() === $parkingId &&
                $s->getStatus() === 'active' &&
                $s->getStartDate() <= $date &&
                ($s->getEndDate() === null || $s->getEndDate() >= $date)
        );
    }

    public function findByParkingIdAndMonth(int $parkingId, int $year, int $month): array
    {
        $start = new DateTimeImmutable("$year-$month-01");
        $end = $start->modify('last day of this month');

        return array_filter(
            array_map([$this, 'mapToSubscription'], $this->storage->read()),
            fn ($s) =>
                $s->getParkingId() === $parkingId &&
                $s->getStartDate() <= $end &&
                ($s->getEndDate() === null || $s->getEndDate() >= $start)
        );
    }

    public function save(Subscription $subscription): Subscription
    {
        $data = $this->storage->read();
        $found = false;

        foreach ($data as &$row) {
            if ($row['id'] === $subscription->getSubscriptionId()) {
                $row = $this->mapFromSubscription($subscription);
                $found = true;
                break;
            }
        }

        if (!$found) {
            $data[] = $this->mapFromSubscription($subscription);
        }

        $this->storage->write($data);

        return $subscription;
    }

    private function mapToSubscription(array $row): Subscription
    {
        return new Subscription(
            id: (int) $row['id'],
            userId: $row['user_id'],
            parkingId: (int) $row['parking_id'],
            typeId: $row['type_id'],
            startDate: new DateTimeImmutable($row['start_date']),
            endDate: $row['end_date'] ? new DateTimeImmutable($row['end_date']) : null,
            status: $row['status'],
            monthlyPrice: (float) $row['monthly_price']
        );
    }

    private function mapFromSubscription(Subscription $subscription): array
    {
        return [
            'id' => $subscription->getSubscriptionId(),
            'user_id' => $subscription->getUserId(),
            'parking_id' => $subscription->getParkingId(),
            'type_id' => $subscription->getTypeId(),
            'start_date' => $subscription->getStartDate()->format('Y-m-d'),
            'end_date' => $subscription->getEndDate()?->format('Y-m-d'),
            'status' => $subscription->getStatus(),
            'monthly_price' => $subscription->getMonthlyPrice(),
        ];
    }
}

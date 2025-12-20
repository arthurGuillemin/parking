<?php

namespace App\Infrastructure\Persistence\Sql;

use App\Domain\Entity\SubscriptionSlot;
use App\Domain\Repository\SubscriptionSlotRepositoryInterface;
use App\Infrastructure\Database\Database;
use PDO;
use PDOException;
use RuntimeException;
use DateTimeImmutable;

class SqlSubscriptionSlotRepository implements SubscriptionSlotRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    //trouver un créneau d'abonnement avec son id
    public function findById(int $id): ?SubscriptionSlot
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, subscriptionTypeId, subscription_id, weekday, start_time, end_time
                FROM subscription_slots
                WHERE id = :id
            ");
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch();

            if (!$row)
                return null;
            return $this->mapToSubscriptionSlot($row);

        } catch (PDOException $e) {
            throw new RuntimeException("aucun créneau trouvé pour cet id: " . $e->getMessage());
        }
    }

    //trouver un créneau d'abonnement avec l'id du type


    public function findBySubscriptionTypeId(int $typeId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, subscriptionTypeId, subscription_id, weekday, start_time, end_time
                FROM subscription_slots
                WHERE subscriptionTypeId = :typeId
                ORDER BY weekday, start_time
            ");
            $stmt->execute(['typeId' => $typeId]);
            $rows = $stmt->fetchAll();

            return array_map([$this, 'mapToSubscriptionSlot'], $rows);

        } catch (PDOException $e) {
            throw new RuntimeException("aucun créneau trouvé pour cet id de type: " . $e->getMessage());
        }
    }

    //save un créneau d'abonnement


    public function save(SubscriptionSlot $slot): SubscriptionSlot
    {
        try {
            $existing = $this->findById($slot->getSubscriptionSlotId());

            if ($existing) {
                $stmt = $this->db->prepare("
                    UPDATE subscription_slots
                    SET subscriptionTypeId = :subscriptionTypeId,
                        weekday = :weekday,
                        start_time = :start_time,
                        end_time = :end_time
                    WHERE id = :id
                ");
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO subscription_slots (id, subscriptionTypeId,subscription_id, weekday, start_time, end_time)
                    VALUES (:id, :subscription_id,:subscriptionTypeId, :weekday, :start_time, :end_time)
                ");
            }

            $stmt->execute([
                'id' => $slot->getSubscriptionSlotId(),
                'subscriptionTypeId' => $slot->getSubscriptionSlotId(),
                'weekday' => $slot->getWeekday(),
                'start_time' => $slot->getStartTime()->format('H:i:s'),
                'end_time' => $slot->getEndTime()->format('H:i:s'),
            ]);

            return $slot;

        } catch (PDOException $e) {
            throw new RuntimeException("erreur dans le save de ce créneau: " . $e->getMessage());
        }
    }

    public function delete(int $id): void
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM subscription_slots WHERE id = :id');
            $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            throw new RuntimeException("erreur lors de la suppression du créneau: " . $e->getMessage());
        }
    }

    private function mapToSubscriptionSlot(array $row): SubscriptionSlot
    {
        return new SubscriptionSlot(
            id: (int) $row['id'],
            subscriptionId: (int) $row['subscription_id'],
            weekdayStart: (int) $row['weekday_start'],
            weekdayEnd: (int) $row['weekday_end'],
            startTime: new DateTimeImmutable($row['start_time']),
            endTime: new DateTimeImmutable($row['end_time'])
        );
    }
}

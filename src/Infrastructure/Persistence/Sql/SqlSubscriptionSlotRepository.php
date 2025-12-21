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
                SELECT id, subscription_id, weekday, start_time, end_time
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

    public function findBySubscriptionTypeId(int $typeId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, subscription_id, weekday, start_time, end_time
                FROM subscription_slots
                WHERE subscription_id = :typeId
                ORDER BY weekday, start_time
            ");
            $stmt->execute(['typeId' => $typeId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map([$this, 'mapToSubscriptionSlot'], $rows);

        } catch (PDOException $e) {
            throw new RuntimeException("aucun créneau trouvé pour cet id de type: " . $e->getMessage());
        }
    }

    public function save(SubscriptionSlot $slot): SubscriptionSlot
    {
        try {
            if ($slot->getSubscriptionSlotId() > 0) {
                // Update
                $stmt = $this->db->prepare("
                    UPDATE subscription_slots
                    SET subscription_id = :typeId,
                        weekday = :weekday,
                        start_time = :start_time,
                        end_time = :end_time
                    WHERE id = :id
                ");
                $stmt->execute([
                    'typeId' => $slot->getSubscriptionTypeId(),
                    'weekday' => $slot->getWeekday(),
                    'start_time' => $slot->getStartTime()->format('H:i:s'),
                    'end_time' => $slot->getEndTime()->format('H:i:s'),
                    'id' => $slot->getSubscriptionSlotId()
                ]);
            } else {
                // Insert
                $stmt = $this->db->prepare("
                    INSERT INTO subscription_slots (subscription_id, weekday, start_time, end_time)
                    VALUES (:typeId, :weekday, :start_time, :end_time)
                ");
                $stmt->execute([
                    'typeId' => $slot->getSubscriptionTypeId(),
                    'weekday' => $slot->getWeekday(),
                    'start_time' => $slot->getStartTime()->format('H:i:s'),
                    'end_time' => $slot->getEndTime()->format('H:i:s')
                ]);
                $id = (int) $this->db->lastInsertId();
                return new SubscriptionSlot(
                    $id,
                    $slot->getSubscriptionTypeId(),
                    $slot->getWeekday(),
                    $slot->getStartTime(),
                    $slot->getEndTime()
                );
            }

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
            subscriptionTypeId: (int) $row['subscription_id'],
            weekday: (int) $row['weekday'],
            startTime: new DateTimeImmutable($row['start_time']),
            endTime: new DateTimeImmutable($row['end_time'])
        );
    }
}

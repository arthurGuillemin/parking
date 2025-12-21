<?php

namespace App\Infrastructure\Persistence\Sql;

use App\Domain\Entity\Subscription;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Infrastructure\Database\Database;
use PDO;
use PDOException;
use RuntimeException;
use DateTimeImmutable;

class SqlSubscriptionRepository implements SubscriptionRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    //trouver un abonnement avec son id
    public function findById(int $id): ?Subscription
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, user_id, parking_id, type_id, start_date, end_date, status, monthly_price
                FROM subscriptions
                WHERE id = :id
            ");
            $stmt->execute(['id' => $id]);

            $row = $stmt->fetch();
            if (!$row)
                return null;

            return $this->mapToSubscription($row);

        } catch (PDOException $e) {
            throw new RuntimeException("aucun abonnement trouvé avec id: " . $e->getMessage());
        }
    }

    //trouver un abonnement avec l'id de l'utilisateur


    public function findByUserId(string $userId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, user_id, parking_id, type_id, start_date, end_date, status, monthly_price
                FROM subscriptions
                WHERE user_id = :user_id
                ORDER BY start_date DESC
            ");
            $stmt->execute(['user_id' => $userId]);

            $rows = $stmt->fetchAll();
            return array_map([$this, 'mapToSubscription'], $rows);

        } catch (PDOException $e) {
            throw new RuntimeException("aucun abonnement trouvé avec cet id utilisateur: " . $e->getMessage());
        }
    }

    public function findActiveByUserId(string $userId): array
    {
        try {
            $today = date('Y-m-d');
            $stmt = $this->db->prepare("
                SELECT id, user_id, parking_id, type_id, start_date, end_date, status, monthly_price
                FROM subscriptions
                WHERE user_id = :user_id
                  AND start_date <= :today
                  AND (end_date IS NULL OR end_date >= :today)
                  AND status = 'active'
            ");
            $stmt->execute([
                'user_id' => $userId,
                'today' => $today
            ]);

            $rows = $stmt->fetchAll();
            return array_map([$this, 'mapToSubscription'], $rows);

        } catch (PDOException $e) {
            throw new RuntimeException("Erreur lors de la recherche des abonnements actifs: " . $e->getMessage());
        }
    }

    //trouver un abonnement actif avec un id utilisateur


    public function findActiveByUserAndParking(string $userId, int $parkingId, DateTimeImmutable $date): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, user_id, parking_id, type_id, start_date, end_date, status, monthly_price
                FROM subscriptions
                WHERE user_id = :user_id
                  AND parking_id = :parking_id
                  AND start_date <= :date
                  AND (end_date IS NULL OR end_date >= :date)
                  AND status = 'active'
            ");
            $stmt->execute([
                'user_id' => $userId,
                'parking_id' => $parkingId,
                'date' => $date->format('Y-m-d'),
            ]);

            $rows = $stmt->fetchAll();
            return array_map([$this, 'mapToSubscription'], $rows);

        } catch (PDOException $e) {
            throw new RuntimeException("aucun abonnement actif trouvé: " . $e->getMessage());
        }
    }

    //trouver un abonnement avec son id pour un mois donné

    public function findByParkingIdAndMonth(int $parkingId, int $year, int $month): array
    {
        try {
            $start = (new DateTimeImmutable("$year-$month-01"))->format('Y-m-d');
            $end = (new DateTimeImmutable("$year-$month-01"))->modify('last day of this month')->format('Y-m-d');

            $stmt = $this->db->prepare("
                SELECT id, user_id, parking_id, type_id, start_date, end_date, status, monthly_price
                FROM subscriptions
                WHERE parking_id = :parking_id
                  AND start_date <= :end
                  AND (end_date IS NULL OR end_date >= :start)
            ");
            $stmt->execute([
                'parking_id' => $parkingId,
                'start' => $start,
                'end' => $end,
            ]);

            $rows = $stmt->fetchAll();
            return array_map([$this, 'mapToSubscription'], $rows);

        } catch (PDOException $e) {
            throw new RuntimeException("aucun abonnement trouvé pour ce mois pour cet utilisateur: " . $e->getMessage());
        }
    }

    //save un abonnement

    public function save(Subscription $subscription): Subscription
    {
        try {
            $existing = $this->findById($subscription->getSubscriptionId());

            if ($existing) {
                $stmt = $this->db->prepare("
                    UPDATE subscriptions
                    SET user_id = :user_id,
                        parking_id = :parking_id,
                        type_id = :type_id,
                        start_date = :start_date,
                        end_date = :end_date,
                        status = :status,
                        monthly_price = :monthly_price
                    WHERE id = :id
                ");
                $stmt->execute([
                    'id' => $subscription->getSubscriptionId(),
                    'user_id' => $subscription->getUserId(),
                    'parking_id' => $subscription->getParkingId(),
                    'type_id' => $subscription->getTypeId(),
                    'start_date' => $subscription->getStartDate()->format('Y-m-d'),
                    'end_date' => $subscription->getEndDate()?->format('Y-m-d'),
                    'status' => $subscription->getStatus(),
                    'monthly_price' => $subscription->getMonthlyPrice(),
                ]);
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO subscriptions (user_id, parking_id, type_id, start_date, end_date, status, monthly_price)
                    VALUES (:user_id, :parking_id, :type_id, :start_date, :end_date, :status, :monthly_price)
                ");
                $stmt->execute([
                    'user_id' => $subscription->getUserId(),
                    'parking_id' => $subscription->getParkingId(),
                    'type_id' => $subscription->getTypeId(),
                    'start_date' => $subscription->getStartDate()->format('Y-m-d'),
                    'end_date' => $subscription->getEndDate()?->format('Y-m-d'),
                    'status' => $subscription->getStatus(),
                    'monthly_price' => $subscription->getMonthlyPrice(),
                ]);
                // We might need to update the object with the new ID?
                // But UseCase usually returns what `save` returns.
                // If `save` returns original object with ID 0, subsequent logic might fail if it expects ID.
                $id = (int) $this->db->lastInsertId();
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

            return $subscription;

        } catch (PDOException $e) {
            throw new RuntimeException("erreur dans le save de l'abonnement: " . $e->getMessage());
        }
    }

    private function mapToSubscription(array $row): Subscription
    {
        return new Subscription(
            id: (int) $row['id'],
            userId: $row['user_id'],
            parkingId: (int) $row['parking_id'],
            typeId: $row['type_id'] !== null ? (int) $row['type_id'] : null,
            startDate: new DateTimeImmutable($row['start_date']),
            endDate: $row['end_date'] !== null ? new DateTimeImmutable($row['end_date']) : null,
            status: $row['status'],
            monthlyPrice: (float) $row['monthly_price']
        );
    }
}

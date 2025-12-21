<?php

namespace App\Infrastructure\Persistence\Sql;

use App\Domain\Entity\SubscriptionType;
use App\Domain\Repository\SubscriptionTypeRepositoryInterface;
use App\Infrastructure\Database\Database;
use PDO;
use PDOException;
use RuntimeException;

class SqlSubscriptionTypeRepository implements SubscriptionTypeRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }


    //trouver un type d'abonnement avec son id
    public function findById(int $id): ?SubscriptionType
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, parking_id, name, description
                FROM subscription_types
                WHERE id = :id
            ");
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch();

            if (!$row)
                return null;

            return $this->mapToSubscriptionType($row);

        } catch (PDOException $e) {
            throw new RuntimeException("aucun type abonnement trouvé avec id: " . $e->getMessage());
        }
    }

    //trouver tous les types d'abonnement
    public function findAll(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT id, parking_id, name, description
                FROM subscription_types
                ORDER BY name
            ");
            $rows = $stmt->fetchAll();

            return array_map([$this, 'mapToSubscriptionType'], $rows);

        } catch (PDOException $e) {
            throw new RuntimeException("erreur dans la recup des types abonnement : " . $e->getMessage());
        }
    }

    //save un type d'abonnement
    public function save(SubscriptionType $type): SubscriptionType
    {
        try {
            $existing = $this->findById($type->getSubscriptionTypeId());

            if ($existing) {
                $stmt = $this->db->prepare("
                    UPDATE subscription_types
                    SET parking_id = :parkingId,
                        name = :name,
                        description = :description
                    WHERE id = :id
                ");
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO subscription_types (parking_id, name, description)
                    VALUES (:parkingId, :name, :description)
                ");
            }

            if ($existing) {
                $stmt->execute([
                    'id' => $type->getSubscriptionTypeId(),
                    'parkingId' => $type->getParkingId(),
                    'name' => $type->getName(),
                    'description' => $type->getDescription(),
                ]);
            } else {
                $stmt->execute([
                    'parkingId' => $type->getParkingId(),
                    'name' => $type->getName(),
                    'description' => $type->getDescription(),
                ]);
                $id = (int) $this->db->lastInsertId();
                return new SubscriptionType(
                    id: $id,
                    parkingId: $type->getParkingId(),
                    name: $type->getName(),
                    description: $type->getDescription(),
                    monthlyPrice: $type->getMonthlyPrice()
                );
            }

            return $type;

        } catch (PDOException $e) {
            throw new RuntimeException("erreur dans le save du type abonnement: " . $e->getMessage());
        }
    }

    private function mapToSubscriptionType(array $row): SubscriptionType
    {
        return new SubscriptionType(
            id: (int) $row['id'],
            parkingId: (int) $row['parking_id'],
            name: $row['name'],
            description: $row['description'],
            monthlyPrice: 0.0
        );
    }
}

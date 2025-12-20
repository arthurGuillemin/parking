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
                SELECT id, name, description, monthly_price
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
                SELECT id, name, description, monthly_price
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
                    SET name = :name,
                        description = :description,
                        monthly_price = :monthly_price
                    WHERE id = :id
                ");
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO subscription_types (id, name, description, monthly_price)
                    VALUES (:id, :name, :description, :monthly_price)
                ");
            }

            $stmt->execute([
                'id' => $type->getSubscriptionTypeId(),
                'name' => $type->getName(),
                'description' => $type->getDescription(),
                'monthly_price' => $type->getMonthlyPrice(),
            ]);

            return $type;

        } catch (PDOException $e) {
            throw new RuntimeException("erreur dans le save du type abonnement: " . $e->getMessage());
        }
    }

    private function mapToSubscriptionType(array $row): SubscriptionType
    {
        return new SubscriptionType(
            id: (int) $row['id'],
            parkingId: 0, // Still 0 as per schema
            name: $row['name'],
            description: $row['description'],
            monthlyPrice: (float) $row['monthly_price']
        );
    }
}

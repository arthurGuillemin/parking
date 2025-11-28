<?php

namespace App\Infrastructure\Persistence\Sql;

use App\Domain\Entity\Parking;
use App\Domain\Repository\ParkingRepositoryInterface;
use App\Infrastructure\Database\Database;
use PDO;
use PDOException;
use RuntimeException;

class SqlParkingRepository implements ParkingRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    // trouver un parking avec soin id
    public function findById(int $id): ?Parking
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, owner_id, name, address, latitude, longitude, total_capacity, open_24_7
                FROM parkings
                WHERE id = :id
            ");
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch();
            if (!$row) {
                return null;
            }
            return $this->mapToParking($row);
        } catch (PDOException $e) {
            throw new RuntimeException("aucu parking trouvé avec cet id: " . $e->getMessage());
        }
    }

    public function findAll(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT id, owner_id, name, address, latitude, longitude, total_capacity, open_24_7
                FROM parkings
            ");
            $rows = $stmt->fetchAll();
            return array_map([$this, 'mapToParking'], $rows);
        } catch (PDOException $e) {
            throw new RuntimeException("erreur dans le recuperation de tout les parkings: " . $e->getMessage());
        }
    }

    public function findByOwnerId(string $ownerId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, owner_id, name, address, latitude, longitude, total_capacity, open_24_7
                FROM parkings
                WHERE owner_id = :owner_id
            ");
            $stmt->execute(['owner_id' => $ownerId]);
            $rows = $stmt->fetchAll();
            return array_map([$this, 'mapToParking'], $rows);
        } catch (PDOException $e) {
            throw new RuntimeException("aucun parking trouvé correspondant a ce proprietaire: " . $e->getMessage());
        }
    }

    public function save(Parking $parking): Parking
    {
        try {
            // checker si le parking n'exisste pas deja
            $existing = $this->findById($parking->getParkingId());

            if ($existing) {
                // si oui on update
                $stmt = $this->db->prepare("
                    UPDATE parkings
                    SET owner_id = :owner_id,
                        name = :name,
                        address = :address,
                        latitude = :latitude,
                        longitude = :longitude,
                        total_capacity = :total_capacity,
                        open_24_7 = :open_24_7
                    WHERE id = :id
                ");
            } else {
                // sinon on insert
                $stmt = $this->db->prepare("
                    INSERT INTO parkings (id, owner_id, name, address, latitude, longitude, total_capacity, open_24_7)
                    VALUES (:id, :owner_id, :name, :address, :latitude, :longitude, :total_capacity, :open_24_7)
                ");
            }
            $stmt->execute([
                'id'            => $parking->getParkingId(),
                'owner_id'      => $parking->getOwnerId(),
                'name'          => $parking->getName(),
                'address'       => $parking->getAddress(),
                'latitude'      => $parking->getLatitude(),
                'longitude'     => $parking->getLongitude(),
                'total_capacity'=> $parking->getTotalCapacity(),
                'open_24_7'     => $parking->isOpen24_7() ? 1 : 0, 
            ]);
            return $parking;
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to save parking: " . $e->getMessage());
        }
    }

    private function mapToParking(array $row): Parking
    {
        return new Parking(
            id: (int)$row['id'],
            ownerId: $row['owner_id'],
            name: $row['name'],
            address: $row['address'],
            latitude: (float)$row['latitude'],
            longitude: (float)$row['longitude'],
            totalCapacity: (int)$row['total_capacity'],
            open_24_7: (bool)$row['open_24_7']
        );
    }
}

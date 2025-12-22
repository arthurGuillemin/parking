<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Parking;
use App\Domain\Repository\ParkingRepositoryInterface;
use PDO;

class ParkingRepository implements ParkingRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?Parking
    {
        $stmt = $this->pdo->prepare('SELECT * FROM parkings WHERE id = ?');
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->mapToEntity($data) : null;
    }

    public function findByOwnerId(string $ownerId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM parkings WHERE owner_id = ?');
        $stmt->execute([$ownerId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => $this->mapToEntity($row), $rows);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM parkings');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => $this->mapToEntity($row), $rows);
    }

    public function save(Parking $parking): Parking
    {
        if ($parking->getParkingId() === 0) {
            return $this->insert($parking);
        } else {
            return $this->update($parking);
        }
    }

    private function insert(Parking $parking): Parking
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO parkings 
             (owner_id, name, address, latitude, longitude, total_capacity, open_24_7) 
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $parking->getOwnerId(),
            $parking->getName(),
            $parking->getAddress(),
            $parking->getLatitude(),
            $parking->getLongitude(),
            $parking->getTotalCapacity(),
            $parking->isOpen24_7() ? 1 : 0
        ]);

        $id = (int) $this->pdo->lastInsertId();

        return new Parking(
            $id,
            $parking->getOwnerId(),
            $parking->getName(),
            $parking->getAddress(),
            $parking->getLatitude(),
            $parking->getLongitude(),
            $parking->getTotalCapacity(),
            $parking->isOpen24_7()
        );
    }

    private function update(Parking $parking): Parking
    {
        $stmt = $this->pdo->prepare(
            'UPDATE parkings 
             SET owner_id=?, name=?, address=?, latitude=?, longitude=?, total_capacity=?, open_24_7=? 
             WHERE id=?'
        );
        $stmt->execute([
            $parking->getOwnerId(),
            $parking->getName(),
            $parking->getAddress(),
            $parking->getLatitude(),
            $parking->getLongitude(),
            $parking->getTotalCapacity(),
            $parking->isOpen24_7() ? 1 : 0,
            $parking->getParkingId()
        ]);
        return $parking;
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM parkings WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function findNearby(float $lat, float $lng, float $radiusKm): array
    {
        // Utilisation de la formule de Haversine pour calculer la distance entre des coordonnées GPS
        // 6371 est le rayon de la Terre en kilomètres
        $sql = '
            SELECT *, 
                   (6371 * ACOS(
                       COS(RADIANS(:lat)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(:lng)) +
                       SIN(RADIANS(:lat2)) * SIN(RADIANS(latitude))
                   )) AS distance
            FROM parkings
            HAVING distance <= :radius
            ORDER BY distance
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'lat' => $lat,
            'lng' => $lng,
            'lat2' => $lat,
            'radius' => $radiusKm
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => $this->mapToEntity($row), $rows);
    }

    private function mapToEntity(array $data): Parking
    {
        return new Parking(
            (int) $data['id'],
            $data['owner_id'],
            $data['name'],
            $data['address'],
            (float) $data['latitude'],
            (float) $data['longitude'],
            (int) $data['total_capacity'],
            (bool) $data['open_24_7']
        );
    }
}

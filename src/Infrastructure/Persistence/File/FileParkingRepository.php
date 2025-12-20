<?php

namespace App\Infrastructure\Persistence\File;

use App\Domain\Entity\Parking;
use App\Domain\Repository\ParkingRepositoryInterface;
use App\Infrastructure\Storage\FileStorage;


class FileParkingRepository implements ParkingRepositoryInterface
{
    private FileStorage $storage;

    public function __construct()
    {
        $this->storage = new FileStorage(__DIR__ . '/storage/parkings.json');
    }

  
    public function findById(int $id): ?Parking
    {
        foreach ($this->storage->read() as $row) {
            if ($row['id'] === $id) {
                return $this->mapToParking($row);
            }
        }

        return null;
    }


    public function findAll(): array
    {
        return array_map(
            [$this, 'mapToParking'],
            $this->storage->read()
        );
    }

    public function findByOwnerId(string $ownerId): array
    {
        $results = [];

        foreach ($this->storage->read() as $row) {
            if (($row['owner_id'] ?? null) === $ownerId) {
                $results[] = $this->mapToParking($row);
            }
        }

        return $results;
    }

    /**
     * Sauvegarde un parking
     * (update si existant, insert sinon)
     */
    public function save(Parking $parking): Parking
    {
        $data = $this->storage->read();
        $found = false;

        foreach ($data as &$row) {
            if ($row['id'] === $parking->getParkingId()) {
                $row = $this->mapFromParking($parking);
                $found = true;
                break;
            }
        }

        if (!$found) {
            $data[] = $this->mapFromParking($parking);
        }

        $this->storage->write($data);

        return $parking;
    }


    private function mapToParking(array $row): Parking
    {
        return new Parking(
            id: (int) $row['id'],
            ownerId: $row['owner_id'],
            name: $row['name'],
            address: $row['address'],
            latitude: (float) $row['latitude'],
            longitude: (float) $row['longitude'],
            totalCapacity: (int) $row['total_capacity'],
            open_24_7: (bool) $row['open_24_7']
        );
    }

    private function mapFromParking(Parking $parking): array
    {
        return [
            'id' => $parking->getParkingId(),
            'owner_id' => $parking->getOwnerId(),
            'name' => $parking->getName(),
            'address' => $parking->getAddress(),
            'latitude' => $parking->getLatitude(),
            'longitude' => $parking->getLongitude(),
            'total_capacity' => $parking->getTotalCapacity(),
            'open_24_7' => $parking->isOpen24_7(),
        ];
    }
}

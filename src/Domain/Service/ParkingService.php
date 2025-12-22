<?php

namespace App\Domain\Service;

use App\Application\UseCase\Owner\AddParking\AddParkingRequest;
use App\Application\UseCase\Owner\AddParking\AddParkingUseCase;
use App\Domain\Entity\Parking;
use App\Domain\Repository\ParkingRepositoryInterface;

class ParkingService
{
    private ParkingRepositoryInterface $parkingRepository;
    private AddParkingUseCase $addParkingUseCase;

    public function __construct(ParkingRepositoryInterface $parkingRepository)
    {
        $this->parkingRepository = $parkingRepository;
        $this->addParkingUseCase = new AddParkingUseCase($parkingRepository);
    }

    public function addParking(string $ownerId, string $name, string $address, float $latitude, float $longitude, int $totalCapacity, bool $open_24_7 = false): Parking
    {
        return $this->addParkingUseCase->execute(new AddParkingRequest($ownerId, $name, $address, $latitude, $longitude, $totalCapacity, $open_24_7));
    }

    public function updateParking(int $id, array $data): Parking
    {
        $parking = $this->parkingRepository->findById($id);
        if (!$parking) {
            throw new \RuntimeException('Parking non trouvé');
        }

        $updatedParking = new Parking(
            $parking->getParkingId(),
            $parking->getOwnerId(),
            $data['name'] ?? $parking->getName(),
            $data['address'] ?? $parking->getAddress(),
            isset($data['latitude']) ? (float) $data['latitude'] : $parking->getLatitude(),
            isset($data['longitude']) ? (float) $data['longitude'] : $parking->getLongitude(),
            isset($data['totalCapacity']) ? (int) $data['totalCapacity'] : $parking->getTotalCapacity(),
            isset($data['open_24_7']) ? (bool) $data['open_24_7'] : $parking->isOpen24_7()
        );

        return $this->parkingRepository->save($updatedParking);
    }

    public function getParkingsByOwner(string $ownerId): array
    {
        return $this->parkingRepository->findByOwnerId($ownerId);
    }

    public function getParkingById(int $id): ?Parking
    {
        return $this->parkingRepository->findById($id);
    }

    public function getAllParkings(): array
    {
        return $this->parkingRepository->findAll();
    }

    public function searchNearby(float $lat, float $lng, float $radiusKm = 20): array
    {
        return $this->parkingRepository->findNearby($lat, $lng, $radiusKm);
    }

    public function searchByText(string $query): array
    {
        return $this->parkingRepository->searchByText($query);
    }
}


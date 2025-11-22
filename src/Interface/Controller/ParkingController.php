<?php

namespace App\Interface\Controller;

use App\Domain\Service\ParkingService;
use Exception;

class ParkingController
{
    private ParkingService $parkingService;

    public function __construct(ParkingService $parkingService)
    {
        $this->parkingService = $parkingService;
    }

    public function add(array $data): array
    {
        if (empty($data['ownerId']) || empty($data['name']) || empty($data['address']) || !isset($data['latitude']) || !isset($data['longitude']) || !isset($data['totalCapacity'])) {
            throw new Exception('Champs requis manquants');
        }
        $open_24_7 = $data['open_24_7'] ?? false;
        $parking = $this->parkingService->addParking(
            $data['ownerId'],
            $data['name'],
            $data['address'],
            (float)$data['latitude'],
            (float)$data['longitude'],
            (int)$data['totalCapacity'],
            (bool)$open_24_7
        );
        return [
            'id' => $parking->getParkingId(),
            'ownerId' => $parking->getOwnerId(),
            'name' => $parking->getName(),
            'address' => $parking->getAddress(),
            'latitude' => $parking->getLatitude(),
            'longitude' => $parking->getLongitude(),
            'totalCapacity' => $parking->getTotalCapacity(),
            'open_24_7' => $parking->isOpen24_7(),
        ];
    }
}

<?php

namespace App\Interface\Controller;

use App\Domain\Service\ParkingAvailabilityService;
use App\Application\UseCase\Owner\GetAvailableSpots\GetAvailableSpotsRequest;
use Exception;

class ParkingAvailabilityController
{
    private ParkingAvailabilityService $parkingAvailabilityService;

    public function __construct(ParkingAvailabilityService $parkingAvailabilityService)
    {
        $this->parkingAvailabilityService = $parkingAvailabilityService;
    }

    public function getAvailableSpots(array $data): array
    {
        if (empty($data['parkingId']) || empty($data['at'])) {
            throw new Exception('Les champs parkingId et at sont obligatoires.');
        }
        $request = new GetAvailableSpotsRequest((int)$data['parkingId'], new \DateTimeImmutable($data['at']));
        $available = $this->parkingAvailabilityService->getAvailableSpots($request);
        return [
            'parkingId' => $data['parkingId'],
            'at' => $data['at'],
            'availableSpots' => $available
        ];
    }
}


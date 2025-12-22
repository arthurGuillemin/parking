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

    public function getAvailableSpots(array $data): void
    {
        if (ob_get_length()) {
            ob_clean();
        }

        header('Content-Type: application/json; charset=UTF-8');

        if (empty($data['parkingId']) || empty($data['at'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Les champs sont obligatoires.']);
            return;
        }

        $request = new GetAvailableSpotsRequest((int) $data['parkingId'], new \DateTimeImmutable($data['at']));
        $available = $this->parkingAvailabilityService->getAvailableSpots($request);

        echo json_encode([
            'parkingId' => $data['parkingId'],
            'at' => $data['at'],
            'availableSpots' => $available
        ], JSON_UNESCAPED_UNICODE);
    }
}


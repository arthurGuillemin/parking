<?php

namespace App\Domain\Service;

use App\Application\DTO\Parking\CountAvailableParkingSpots\CountAvailableParkingSpotsRequest;
use App\Application\UseCase\Parking\CountAvailableParkingSpots\CountAvailableParkingSpotsUseCase;

class ParkingSpotCounterService
{
    private CountAvailableParkingSpotsUseCase $countAvailableParkingSpotsUseCase;

    public function __construct(CountAvailableParkingSpotsUseCase $countAvailableParkingSpotsUseCase)
    {
        $this->countAvailableParkingSpotsUseCase = $countAvailableParkingSpotsUseCase;
    }

    public function getAvailableSpots(int $parkingId, \DateTimeImmutable $at): int
    {
        $request = new CountAvailableParkingSpotsRequest($parkingId, $at);
        $response = $this->countAvailableParkingSpotsUseCase->execute($request);
        return $response->availableSpots;
    }
}

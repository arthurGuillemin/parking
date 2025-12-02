<?php

namespace App\Domain\Service;

use App\Application\UseCase\Parking\CountAvailableParkingSpotsUseCase\CountAvailableParkingSpotsUseCase;
use App\Application\UseCase\Parking\CountAvailableParkingSpotsUseCase\CountAvailableParkingSpotsRequest;

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
        return $this->countAvailableParkingSpotsUseCase->execute($request);
    }
}

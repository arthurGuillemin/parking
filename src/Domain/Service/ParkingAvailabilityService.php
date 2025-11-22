<?php

namespace App\Domain\Service;

use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Application\UseCase\Owner\GetAvailableSpots\GetAvailableSpotsUseCase;
use App\Application\UseCase\Owner\GetAvailableSpots\GetAvailableSpotsRequest;

class ParkingAvailabilityService
{
    private GetAvailableSpotsUseCase $getAvailableSpotsUseCase;

    public function __construct(ParkingRepositoryInterface $parkingRepository, ParkingSessionRepositoryInterface $parkingSessionRepository)
    {
        $this->getAvailableSpotsUseCase = new GetAvailableSpotsUseCase($parkingRepository, $parkingSessionRepository);
    }

    public function getAvailableSpots(GetAvailableSpotsRequest $request): int
    {
        return $this->getAvailableSpotsUseCase->execute($request);
    }
}


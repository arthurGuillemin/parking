<?php

namespace App\Application\UseCase\Parking\CheckParkingAvailability;

use App\Application\DTO\Parking\CheckParkingAvailability\CheckParkingAvailabilityRequest;
use App\Application\DTO\Parking\CheckParkingAvailability\CheckParkingAvailabilityResponse;
use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Service\ParkingAvailabilityService;

class CheckParkingAvailabilityUseCase
{
    private ParkingRepositoryInterface $parkingRepository;
    private ParkingAvailabilityService $availabilityService;

    public function __construct(
        ParkingRepositoryInterface $parkingRepository,
        ParkingAvailabilityService $availabilityService
    ) {
        $this->parkingRepository = $parkingRepository;
        $this->availabilityService = $availabilityService;
    }

    public function execute(CheckParkingAvailabilityRequest $request): CheckParkingAvailabilityResponse
    {
        $parking = $this->parkingRepository->findById($request->parkingId);
        if (!$parking) {
            return new CheckParkingAvailabilityResponse(false, 'Parking non disponible');
        }
        $isAvailable = $this->availabilityService->isAvailable($parking, $request->dateTime);
        return new CheckParkingAvailabilityResponse($isAvailable);
    }
}

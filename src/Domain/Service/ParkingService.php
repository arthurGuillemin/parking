<?php

namespace App\Domain\Service;

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
        return $this->addParkingUseCase->execute($ownerId, $name, $address, $latitude, $longitude, $totalCapacity, $open_24_7);
    }
}


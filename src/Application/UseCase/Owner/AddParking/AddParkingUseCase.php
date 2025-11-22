<?php

namespace App\Application\UseCase\Owner\AddParking;

use App\Domain\Entity\Parking;
use App\Domain\Repository\ParkingRepositoryInterface;
use App\Application\UseCase\Owner\AddParking\AddParkingRequest;

class AddParkingUseCase
{
    private ParkingRepositoryInterface $parkingRepository;

    public function __construct(ParkingRepositoryInterface $parkingRepository)
    {
        $this->parkingRepository = $parkingRepository;
    }

    /**
     * Add a new parking for an owner.
     *
     * @param AddParkingRequest $request
     * @return Parking
     */
    public function execute(AddParkingRequest $request): Parking
    {
        $parking = new Parking(
            0,
            $request->ownerId,
            $request->name,
            $request->address,
            $request->latitude,
            $request->longitude,
            $request->totalCapacity,
            $request->open_24_7
        );
        return $this->parkingRepository->save($parking);
    }
}

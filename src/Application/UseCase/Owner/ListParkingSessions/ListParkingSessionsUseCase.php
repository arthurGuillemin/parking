<?php

namespace App\Application\UseCase\Owner\ListParkingSessions;

use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Entity\ParkingSession;

class ListParkingSessionsUseCase
{
    private ParkingSessionRepositoryInterface $parkingSessionRepository;

    public function __construct(ParkingSessionRepositoryInterface $parkingSessionRepository)
    {
        $this->parkingSessionRepository = $parkingSessionRepository;
    }

    /**
     * Lister les sessions de parking pour un parking.
     *
     * @param ListParkingSessionsRequest $request
     * @return ParkingSession[]
     */
    public function execute(ListParkingSessionsRequest $request): array
    {
        return $this->parkingSessionRepository->findByParkingId($request->parkingId);
    }
}


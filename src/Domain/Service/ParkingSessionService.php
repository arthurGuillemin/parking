<?php

namespace App\Domain\Service;

use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Application\UseCase\Owner\ListParkingSessions\ListParkingSessionsUseCase;
use App\Application\UseCase\Owner\ListParkingSessions\ListParkingSessionsRequest;

class ParkingSessionService
{
    private ParkingSessionRepositoryInterface $parkingSessionRepository;
    private ListParkingSessionsUseCase $listParkingSessionsUseCase;

    public function __construct(ParkingSessionRepositoryInterface $parkingSessionRepository)
    {
        $this->parkingSessionRepository = $parkingSessionRepository;
        $this->listParkingSessionsUseCase = new ListParkingSessionsUseCase($parkingSessionRepository);
    }

    public function listParkingSessions(ListParkingSessionsRequest $request): array
    {
        return $this->listParkingSessionsUseCase->execute($request);
    }
}


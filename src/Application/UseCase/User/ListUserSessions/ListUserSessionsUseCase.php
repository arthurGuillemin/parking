<?php

namespace App\Application\UseCase\User\ListUserSessions;

use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Application\DTO\Response\ParkingSessionResponse;

use App\Domain\Repository\ParkingRepositoryInterface;

class ListUserSessionsUseCase
{
    private ParkingSessionRepositoryInterface $repository;
    private ParkingRepositoryInterface $parkingRepository;

    public function __construct(ParkingSessionRepositoryInterface $repository, ParkingRepositoryInterface $parkingRepository)
    {
        $this->repository = $repository;
        $this->parkingRepository = $parkingRepository;
    }

    public function execute(ListUserSessionsRequest $request): array
    {
        // Check if repository has findByUserId
        $sessions = $this->repository->findByUserId($request->userId);

        return array_map(function ($session) {
            $parking = $this->parkingRepository->findById($session->getParkingId());
            $parkingName = $parking ? $parking->getName() : 'Inconnu';
            return new ParkingSessionResponse($session, $parkingName);
        }, $sessions);
    }
}

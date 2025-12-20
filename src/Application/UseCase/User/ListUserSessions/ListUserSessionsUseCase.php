<?php

namespace App\Application\UseCase\User\ListUserSessions;

use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Application\DTO\Response\ParkingSessionResponse;

class ListUserSessionsUseCase
{
    private ParkingSessionRepositoryInterface $repository;

    public function __construct(ParkingSessionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(ListUserSessionsRequest $request): array
    {
        // Check if repository has findByUserId
        $sessions = $this->repository->findByUserId($request->userId);

        return array_map(function ($session) {
            return new ParkingSessionResponse(
                $session->getSessionId(),
                $session->getParkingId(),
                'ABC-123', // Placeholder until Vehicle entity is added
                $session->getEntryDateTime()->format('Y-m-d H:i:s'),
                $session->getExitDateTime()?->format('Y-m-d H:i:s'),
                $session->getFinalAmount()
            );
        }, $sessions);
    }
}

<?php

namespace App\Domain\Service;

use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Application\UseCase\Owner\ListSessionsOutOfReservationOrSubscription\ListSessionsOutOfReservationOrSubscriptionUseCase;
use App\Application\UseCase\Owner\ListSessionsOutOfReservationOrSubscription\ListSessionsOutOfReservationOrSubscriptionRequest;

class SessionsOutOfReservationOrSubscriptionService
{
    private ListSessionsOutOfReservationOrSubscriptionUseCase $useCase;

    public function __construct(
        ParkingSessionRepositoryInterface $parkingSessionRepository,
        ReservationRepositoryInterface $reservationRepository,
        SubscriptionRepositoryInterface $subscriptionRepository,
        \App\Domain\Service\SubscriptionCoverageService $coverageService
    ) {
        $this->useCase = new ListSessionsOutOfReservationOrSubscriptionUseCase(
            $parkingSessionRepository,
            $reservationRepository,
            $subscriptionRepository,
            $coverageService
        );
    }

    public function listSessions(ListSessionsOutOfReservationOrSubscriptionRequest $request): array
    {
        return $this->useCase->execute($request);
    }
}

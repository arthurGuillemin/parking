<?php

namespace App\Interface\Controller;

use App\Application\UseCase\Owner\ListSessionsOutOfReservationOrSubscription\ListSessionsOutOfReservationOrSubscriptionUseCase;
use App\Application\UseCase\Owner\ListSessionsOutOfReservationOrSubscription\ListSessionsOutOfReservationOrSubscriptionRequest;

class SessionsOutOfReservationOrSubscriptionController
{
    private ListSessionsOutOfReservationOrSubscriptionUseCase $useCase;

    public function __construct(ListSessionsOutOfReservationOrSubscriptionUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function list(array $data): array
    {
        if (empty($data['parkingId'])) {
            throw new \InvalidArgumentException('Le champ parkingId est obligatoire.');
        }

        $request = new ListSessionsOutOfReservationOrSubscriptionRequest((int)$data['parkingId']);
        $sessions = $this->useCase->execute($request);

        return array_map(function ($session) {
            return [
                'id' => $session->getSessionId(),
                'userId' => $session->getUserId(),
                'parkingId' => $session->getParkingId(),
                'reservationId' => $session->getReservationId(),
                'entryDateTime' => $session->getEntryDateTime()->format('Y-m-d H:i:s'),
                'exitDateTime' => $session->getExitDateTime() ? $session->getExitDateTime()->format('Y-m-d H:i:s') : null,
                'finalAmount' => $session->getFinalAmount(),
                'penaltyApplied' => $session->isPenaltyApplied(),
            ];
        }, $sessions);
    }
}

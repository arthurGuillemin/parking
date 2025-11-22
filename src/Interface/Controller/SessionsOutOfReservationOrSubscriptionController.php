<?php

namespace App\Interface\Controller;

use App\Domain\Service\SessionsOutOfReservationOrSubscriptionService;
use App\Application\UseCase\Owner\ListSessionsOutOfReservationOrSubscription\ListSessionsOutOfReservationOrSubscriptionRequest;
use Exception;

class SessionsOutOfReservationOrSubscriptionController
{
    private SessionsOutOfReservationOrSubscriptionService $service;

    public function __construct(SessionsOutOfReservationOrSubscriptionService $service)
    {
        $this->service = $service;
    }

    public function list(array $data): array
    {
        if (empty($data['parkingId'])) {
            throw new Exception('Le champ parkingId est obligatoire.');
        }
        $request = new ListSessionsOutOfReservationOrSubscriptionRequest((int)$data['parkingId']);
        $sessions = $this->service->listSessions($request);
        return array_map(function($session) {
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


<?php

namespace App\Interface\Controller;

use App\Domain\Service\ParkingSessionService;
use App\Application\UseCase\Owner\ListParkingSessions\ListParkingSessionsRequest;
use Exception;

class ParkingSessionController
{
    private ParkingSessionService $parkingSessionService;

    public function __construct(ParkingSessionService $parkingSessionService)
    {
        $this->parkingSessionService = $parkingSessionService;
    }

    public function listByParking(array $data): array
    {
        if (empty($data['parkingId'])) {
            throw new Exception('Le champ parkingId est obligatoire.');
        }
        $request = new ListParkingSessionsRequest((int)$data['parkingId']);
        $sessions = $this->parkingSessionService->listParkingSessions($request);
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


<?php

namespace App\Interface\Controller;

use App\Domain\Service\ReservationService;
use App\Application\UseCase\Owner\ListReservations\ListReservationsRequest;
use Exception;

class ReservationController
{
    private ReservationService $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    public function listByParking(array $data): array
    {
        if (empty($data['parkingId'])) {
            throw new Exception('Le champ parkingId est obligatoire.');
        }
        $start = !empty($data['start']) ? new \DateTimeImmutable($data['start']) : null;
        $end = !empty($data['end']) ? new \DateTimeImmutable($data['end']) : null;
        $request = new ListReservationsRequest((int)$data['parkingId'], $start, $end);
        $reservations = $this->reservationService->listReservations($request);
        return array_map(function($reservation) {
            return [
                'id' => $reservation->getReservationId(),
                'userId' => $reservation->getUserId(),
                'parkingId' => $reservation->getParkingId(),
                'startDateTime' => $reservation->getStartDateTime()->format('Y-m-d H:i:s'),
                'endDateTime' => $reservation->getEndDateTime()->format('Y-m-d H:i:s'),
                'status' => $reservation->getStatus(),
                'calculatedAmount' => $reservation->getCalculatedAmount(),
                'finalAmount' => $reservation->getFinalAmount(),
            ];
        }, $reservations);
    }
}


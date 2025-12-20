<?php

namespace App\Interface\Controller;

use App\Domain\Service\ReservationService;
use App\Application\UseCase\Owner\ListReservations\ListReservationsRequest;
use App\Application\UseCase\User\MakeReservation\MakeReservationUseCase;
use App\Application\UseCase\User\MakeReservation\MakeReservationRequest;
use Exception;

class ReservationController
{
    private ReservationService $reservationService;
    private MakeReservationUseCase $makeReservationUseCase;

    public function __construct(ReservationService $reservationService, MakeReservationUseCase $makeReservationUseCase)
    {
        $this->reservationService = $reservationService;
        $this->makeReservationUseCase = $makeReservationUseCase;
    }

    public function create(array $data): array
    {
        if (empty($data['userId']) || empty($data['parkingId']) || empty($data['start']) || empty($data['end'])) {
            throw new \InvalidArgumentException('Tous les champs (userId, parkingId, start, end) sont obligatoires.');
        }

        $request = new MakeReservationRequest(
            $data['userId'],
            (int) $data['parkingId'],
            new \DateTimeImmutable($data['start']),
            new \DateTimeImmutable($data['end'])
        );

        $response = $this->makeReservationUseCase->execute($request);

        return [
            'id' => $response->id,
            'status' => $response->status,
            'calculatedAmount' => $response->amount,
            'message' => 'Réservation créée avec succès.'
        ];
    }

    public function listByParking(array $data): array
    {
        if (empty($data['parkingId'])) {
            throw new \InvalidArgumentException('Le champ est obligatoire.');
        }
        $start = !empty($data['start']) ? new \DateTimeImmutable($data['start']) : null;
        $end = !empty($data['end']) ? new \DateTimeImmutable($data['end']) : null;
        $request = new ListReservationsRequest((int) $data['parkingId'], $start, $end);
        $reservations = $this->reservationService->listReservations($request);
        return array_map(function ($reservation) {
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

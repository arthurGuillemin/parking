<?php

namespace App\Interface\Controller;

use App\Domain\Service\ReservationService;
use App\Application\UseCase\Owner\ListReservations\ListReservationsRequest;
use App\Application\UseCase\User\MakeReservation\MakeReservationUseCase;
use App\Application\UseCase\User\MakeReservation\MakeReservationRequest;
use Exception;

use App\Domain\Service\JwtService;

class ReservationController
{
    private ReservationService $reservationService;
    private MakeReservationUseCase $makeReservationUseCase;
    private \App\Domain\Service\ParkingService $parkingService;
    private JwtService $jwtService;

    public function __construct(
        ReservationService $reservationService,
        MakeReservationUseCase $makeReservationUseCase,
        \App\Domain\Service\ParkingService $parkingService,
        JwtService $jwtService
    ) {
        $this->reservationService = $reservationService;
        $this->makeReservationUseCase = $makeReservationUseCase;
        $this->parkingService = $parkingService;
        $this->jwtService = $jwtService;
    }

    public function show(): void
    {
        // On pourrait récupérer le parking_id depuis $_GET si nécessaire pour pré-remplir le formulaire
        $parkingId = isset($_GET['parkingId']) ? (int) $_GET['parkingId'] : null;
        if (!$parkingId) {
            header('Location: /parkings');
            exit;
        }

        $parking = $this->parkingService->getParkingById($parkingId);
        if (!$parking) {
            http_response_code(404);
            echo "Parking introuvable.";
            exit;
        }

        require __DIR__ . '/../../../templates/reservation_create.php';
    }

    public function create(array $data): array
    {
        // Get User ID from Token
        $token = $_COOKIE['auth_token'] ?? null;
        if (!$token) {
            header('Location: /login');
            exit;
        }
        $userId = $this->jwtService->validateToken($token);
        if (!$userId) {
            header('Location: /login');
            exit;
        }

        if (empty($data['parkingId']) || empty($data['start']) || empty($data['end'])) {
            throw new \InvalidArgumentException('Tous les champs (parkingId, start, end) sont obligatoires. (User ID override)');
        }

        $request = new MakeReservationRequest(
            $userId,
            (int) $data['parkingId'],
            new \DateTimeImmutable($data['start']),
            new \DateTimeImmutable($data['end'])
        );

        $response = $this->makeReservationUseCase->execute($request);

        header('Location: /dashboard?success=reservation_created');
        exit;
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

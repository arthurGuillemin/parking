<?php

namespace App\Interface\Controller;

use App\Domain\Service\ReservationService;
use App\Application\UseCase\Owner\ListReservations\ListReservationsRequest;
use Exception;

class ReservationController
{
    private ReservationService $reservationService;
    private \App\Domain\Service\ParkingService $parkingService; // Need to fetch parking name etc
    private \App\Domain\Service\JwtService $jwtService;
    private \App\Domain\Repository\UserRepositoryInterface $userRepository; // Add dependency

    public function __construct(
        ReservationService $reservationService,
        \App\Domain\Service\ParkingService $parkingService,
        \App\Domain\Service\JwtService $jwtService,
        \App\Domain\Repository\UserRepositoryInterface $userRepository
    ) {
        $this->reservationService = $reservationService;
        $this->parkingService = $parkingService;
        $this->jwtService = $jwtService;
        $this->userRepository = $userRepository;
    }

    public function show(array $params): void
    {
        $parkingId = $_GET['parkingId'] ?? null;
        if (!$parkingId) {
            header('Location: /parkings');
            return;
        }

        $parking = $this->parkingService->getParkingById((int) $parkingId);
        if (!$parking) {
            die("Parking not found");
        }

        // Pass info to view
        require dirname(__DIR__, 3) . '/templates/reservation_create.php';
    }

    public function create(array $data): void
    {
        // Auth check
        $userId = null;
        if (isset($_COOKIE['auth_token'])) {
            $payload = $this->jwtService->decode($_COOKIE['auth_token']);
            if ($payload) {
                $userId = $payload['user_id'] ?? null;
            }
        }

        // Verify USER exists (prevent Owner/User conflict)
        if ($userId) {
            $user = $this->userRepository->findById($userId);
            if (!$user) {
                // ID exists in token but not in Users table -> Likely an Owner or invalid token.
                $userId = null;
            }
        }

        if (!$userId) {
            // Redirect to login with return url?
            header('Location: /login?error=auth_required'); // Make it explicit
            return;
        }

        try {
            $parkingId = (int) $_POST['parkingId'];
            $start = new \DateTimeImmutable($_POST['start']);
            $end = new \DateTimeImmutable($_POST['end']);

            $reservation = $this->reservationService->createReservation($parkingId, $userId, $start, $end);

            // Redirect to success or list
            // For now simple echo
            // header('Location: /reservation/list'); 
            echo "Réservation confirmée ! Prix estimé : " . $reservation->getCalculatedAmount() . "€";

        } catch (\Exception $e) {
            die("Erreur: " . $e->getMessage());
        }
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


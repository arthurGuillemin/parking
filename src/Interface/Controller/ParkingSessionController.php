<?php

namespace App\Interface\Controller;

use App\Domain\Service\ParkingSessionService;
use App\Application\UseCase\Owner\ListParkingSessions\ListParkingSessionsRequest;
use Exception;

class ParkingSessionController
{
    private ParkingSessionService $parkingSessionService;
    private $enterParkingUseCase;
    private $exitParkingUseCase;
    private $listReservationsUseCase;
    private $listSubscriptionsUseCase;
    private $jwtService;
    private $sessionRepository;
    private $reservationRepository;
    private $subscriptionRepository;

    public function __construct(
        ParkingSessionService $parkingSessionService,
        \App\Application\UseCase\User\EnterParking\EnterParkingUseCase $enterParkingUseCase,
        \App\Application\UseCase\User\ExitParking\ExitParkingUseCase $exitParkingUseCase,
        \App\Application\UseCase\User\ListUserReservations\ListUserReservationsUseCase $listReservationsUseCase,
        \App\Application\UseCase\User\ListUserSubscriptions\ListUserSubscriptionsUseCase $listSubscriptionsUseCase,
        \App\Domain\Service\JwtService $jwtService,
        \App\Domain\Repository\ParkingSessionRepositoryInterface $sessionRepository,
        \App\Domain\Repository\ReservationRepositoryInterface $reservationRepository,
        \App\Domain\Repository\SubscriptionRepositoryInterface $subscriptionRepository
    ) {
        $this->parkingSessionService = $parkingSessionService;
        $this->enterParkingUseCase = $enterParkingUseCase;
        $this->exitParkingUseCase = $exitParkingUseCase;
        $this->listReservationsUseCase = $listReservationsUseCase;
        $this->listSubscriptionsUseCase = $listSubscriptionsUseCase;
        $this->jwtService = $jwtService;
        $this->sessionRepository = $sessionRepository;
        $this->reservationRepository = $reservationRepository;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function listByParking(array $data): array
    {
        if (empty($data['parkingId'])) {
            throw new \InvalidArgumentException('Le champ est obligatoire.');
        }
        $request = new ListParkingSessionsRequest((int) $data['parkingId']);
        $sessions = $this->parkingSessionService->listParkingSessions($request);
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

    public function simulation()
    {
        $userId = $this->getUserId();
        if (!$userId)
            return;

        $success = $_GET['success'] ?? null;
        $error = $_GET['error'] ?? null;

        $activeSession = $this->sessionRepository->findActiveSessionByUserId($userId);
        $reservations = $this->listReservationsUseCase->execute(
            new \App\Application\UseCase\User\ListUserReservations\ListUserReservationsRequest($userId)
        );
        $subscriptions = $this->listSubscriptionsUseCase->execute(
            new \App\Application\UseCase\User\ListUserSubscriptions\ListUserSubscriptionsRequest($userId)
        );

        require dirname(__DIR__, 3) . '/templates/simulation.php';
    }

    public function enter()
    {
        $userId = $this->getUserId();
        if (!$userId)
            return;

        $parkingId = $_POST['parking_id'] ?? null;
        $reservationId = $_POST['reservation_id'] ?? null;

        try {
            if ($reservationId) {
                // Fetch reservation to get Parking ID
                $res = $this->reservationRepository->findById((int) $reservationId);
                if (!$res) {
                    throw new Exception("Réservation introuvable.");
                }
                $parkingId = $res->getParkingId();
                $reservationId = (int) $reservationId;
            } else {
                if (!$parkingId) {
                    throw new Exception("Aucun parking sélectionné.");
                }
                $parkingId = (int) $parkingId;
                $reservationId = null;
            }

            $request = new \App\Application\UseCase\User\EnterParking\EnterParkingRequest($userId, $parkingId, $reservationId);
            $this->enterParkingUseCase->execute($request);

            header('Location: /simulation?success=' . urlencode("Bienvenue ! Barrière ouverte."));
            exit;

        } catch (Exception $e) {
            header('Location: /simulation?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    public function exit()
    {
        $userId = $this->getUserId();
        if (!$userId)
            return;

        $parkingId = $_POST['parking_id'] ?? null;

        try {
            if (!$parkingId)
                throw new Exception("Impossible d'identifier le parking de sortie.");

            $request = new \App\Application\UseCase\User\ExitParking\ExitParkingRequest($userId, (int) $parkingId);
            $session = $this->exitParkingUseCase->execute($request);

            $msg = "Au revoir ! Sortie validée.";
            if ($session->amount > 0) {
                $msg .= " Montant final : " . number_format($session->amount, 2) . " €. ";
                if ($session->penaltyApplied) {
                    $msg .= "(Pénalité de dépassement incluse).";
                }
            }

            header('Location: /simulation?success=' . urlencode($msg));
            exit;

        } catch (Exception $e) {
            header('Location: /simulation?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    private function getUserId(): ?string
    {
        $token = $_COOKIE['auth_token'] ?? null;
        if (!$token) {
            header('Location: /login');
            exit;
        }
        return $this->jwtService->validateToken($token);
    }
}


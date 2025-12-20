<?php

namespace App\Interface\Controller;

use App\Application\UseCase\User\ListUserReservations\ListUserReservationsUseCase;
use App\Application\UseCase\User\ListUserReservations\ListUserReservationsRequest;
use App\Application\UseCase\User\ListUserSessions\ListUserSessionsUseCase;
use App\Application\UseCase\User\ListUserSessions\ListUserSessionsRequest;
use App\Application\UseCase\User\ListUserSubscriptions\ListUserSubscriptionsUseCase;
use App\Application\UseCase\User\ListUserSubscriptions\ListUserSubscriptionsRequest;
use App\Domain\Service\JwtService;

class UserController
{
    private ListUserReservationsUseCase $listReservationsUseCase;
    private ListUserSessionsUseCase $listSessionsUseCase;
    private ListUserSubscriptionsUseCase $listSubscriptionsUseCase;
    private \App\Application\UseCase\User\ListUserInvoices\ListUserInvoicesUseCase $listInvoicesUseCase;
    private JwtService $jwtService;

    public function __construct(
        ListUserReservationsUseCase $listReservationsUseCase,
        ListUserSessionsUseCase $listSessionsUseCase,
        ListUserSubscriptionsUseCase $listSubscriptionsUseCase,
        \App\Application\UseCase\User\ListUserInvoices\ListUserInvoicesUseCase $listInvoicesUseCase,
        JwtService $jwtService
    ) {
        $this->listReservationsUseCase = $listReservationsUseCase;
        $this->listSessionsUseCase = $listSessionsUseCase;
        $this->listSubscriptionsUseCase = $listSubscriptionsUseCase;
        $this->listInvoicesUseCase = $listInvoicesUseCase;
        $this->jwtService = $jwtService;
    }

    public function dashboard()
    {
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

        $reservations = $this->listReservationsUseCase->execute(
            new ListUserReservationsRequest($userId)
        );

        $sessions = $this->listSessionsUseCase->execute(
            new ListUserSessionsRequest($userId)
        );

        $subscriptions = $this->listSubscriptionsUseCase->execute(
            new ListUserSubscriptionsRequest($userId)
        );

        $invoices = $this->listInvoicesUseCase->execute(
            new \App\Application\UseCase\User\ListUserInvoices\ListUserInvoicesRequest($userId)
        );

        require dirname(__DIR__, 3) . '/templates/user_dashboard.php';
    }
}

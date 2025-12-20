<?php

namespace App\Interface\Middleware;

use App\Domain\Service\JwtService;

class AuthenticationMiddleware
{
    private JwtService $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function handle(): bool
    {
        // 1. Vérifie d'abord le header Authorization
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = null;
        if (preg_match('/Bearer\s(.+)/', $authHeader, $matches)) {
            $token = $matches[1];
        } else {
            // 2. Fallback sur la session (pour compatibilité)
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $token = $_SESSION['jwt_token'] ?? null;
        }

        if (!$token || !$this->jwtService->decode($token)) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return false; // Arrête le traitement
        }

        return true; // Continue vers le contrôleur
    }
}

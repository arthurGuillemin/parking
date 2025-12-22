<?php

namespace App\Interface\Controller;

use App\Domain\Service\OwnerService;
use App\Domain\Security\XssProtectionService;
use Exception;

class OwnerController
{
    private OwnerService $ownerService;
    private XssProtectionService $xssProtection;
    private \App\Domain\Service\JwtService $jwtService;

    public function __construct(OwnerService $ownerService, XssProtectionService $xssProtection, \App\Domain\Service\JwtService $jwtService)
    {
        $this->ownerService = $ownerService;
        $this->xssProtection = $xssProtection;
        $this->jwtService = $jwtService;
    }

    public function dashboard(): void
    {
        $this->checkAuth();
        require dirname(__DIR__, 3) . '/templates/owner_dashboard.php';
    }

    public function registerForm(): void
    {
        require dirname(__DIR__, 3) . '/templates/owner_register_form.php';
    }

    /**
     * Inscription d'un propriétaire
     */
    public function register(array $data = []): array
    {
        header('Content-Type: application/json; charset=UTF-8');

        $data = $this->parseRequestData($data);
        $sanitizedData = $this->sanitizeOwnerData($data);

        if (!$this->validateOwnerFields($sanitizedData)) {
            return [];
        }

        try {
            $owner = $this->ownerService->register(
                $sanitizedData['email'],
                $data['password'],
                $sanitizedData['firstName'],
                $sanitizedData['lastName']
            );

            return $this->sendOwnerSuccessResponse($owner);
        } catch (\InvalidArgumentException $e) {
            return $this->sendJsonError(400, $this->xssProtection->sanitize($e->getMessage()));
        } catch (\Exception $e) {
            error_log('Owner registration error: ' . $e->getMessage());
            return $this->sendJsonError(500, 'Une erreur est survenue lors de l\'inscription');
        }
    }

    public function loginForm(): void
    {
        require dirname(__DIR__, 3) . '/templates/owner_login.php';
    }

    /**
     * Connexion d'un propriétaire
     */
    public function login(array $data = []): ?array
    {
        header('Content-Type: application/json; charset=UTF-8');

        $data = $this->parseRequestData($data);
        $email = $this->xssProtection->sanitizeEmail($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (!$email || empty($password)) {
            $this->sendJsonError(400, 'Champs requis manquants');
            return null;
        }

        try {
            $loginResponse = $this->ownerService->authenticate($email, $password);

            if ($loginResponse) {
                return $this->sendLoginSuccessResponse($loginResponse);
            }

            $this->sendJsonError(401, 'Identifiants invalides');
            return null;
        } catch (\Exception $e) {
            error_log('Owner login error: ' . $e->getMessage());
            $this->sendJsonError(500, 'Une erreur est survenue lors de la connexion');
            return null;
        }
    }

    private function checkAuth(): void
    {
        if (!isset($_COOKIE['auth_token']) || !$this->jwtService->validateToken($_COOKIE['auth_token'])) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Parse les données de la requête (JSON, POST ou array)
     */
    private function parseRequestData(array $data): array
    {
        if (empty($data)) {
            $input = file_get_contents('php://input');
            $jsonData = json_decode($input, true);
            return $jsonData ?: $_POST;
        }
        return $data;
    }

    /**
     * Sanitize les données du propriétaire
     */
    private function sanitizeOwnerData(array $data): array
    {
        return [
            'email' => $this->xssProtection->sanitizeEmail($data['email'] ?? ''),
            'firstName' => $this->xssProtection->sanitize($data['firstName'] ?? $data['first_name'] ?? ''),
            'lastName' => $this->xssProtection->sanitize($data['lastName'] ?? $data['last_name'] ?? ''),
            'password' => $data['password'] ?? '',
        ];
    }

    /**
     * Valide les champs obligatoires
     */
    private function validateOwnerFields(array $data): bool
    {
        if (!$data['email'] || empty($data['password']) || empty($data['firstName']) || empty($data['lastName'])) {
            $this->sendJsonError(400, 'Champs requis manquants');
            return false;
        }
        return true;
    }

    /**
     * Envoie la réponse de succès pour l'inscription
     */
    private function sendOwnerSuccessResponse($owner): array
    {
        http_response_code(201);
        $response = [
            'id' => $owner->getOwnerId(),
            'email' => $owner->getEmail(),
            'firstName' => $owner->getFirstName(),
            'lastName' => $owner->getLastName(),
        ];
        return $response;
    }

    /**
     * Envoie la réponse de succès pour la connexion
     */
    private function sendLoginSuccessResponse($loginResponse): array
    {
        http_response_code(200);
        $response = [
            'token' => $loginResponse->token,
            'refreshToken' => $loginResponse->refreshToken,
            'expiresIn' => $loginResponse->expiresIn,
            'role' => $loginResponse->role,
        ];
        return $response;
    }

    /**
     * Envoie une erreur JSON
     */
    private function sendJsonError(int $code, string $message): array
    {
        http_response_code($code);
        return ['error' => $message];
    }
}

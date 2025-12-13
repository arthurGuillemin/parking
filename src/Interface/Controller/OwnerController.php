<?php

namespace App\Interface\Controller;

use App\Domain\Service\OwnerService;
use App\Domain\Security\XssProtectionService;
use Exception;

class OwnerController
{
    private OwnerService $ownerService;
    private XssProtectionService $xssProtection;

    public function __construct(OwnerService $ownerService, XssProtectionService $xssProtection)
    {
        $this->ownerService = $ownerService;
        $this->xssProtection = $xssProtection;
    }

    public function registerForm(): void
    {
        require __DIR__ . '/../../../../templates/owner_register_form.php';
    }

    public function register(array $data = []): array
    {
        header('Content-Type: application/json; charset=UTF-8');

        // Récupération des données (JSON ou POST ou array)
        if (empty($data)) {
            $input = file_get_contents('php://input');
            $jsonData = json_decode($input, true);
            $data = $jsonData ?: $_POST;
        }

        // Protection anti-XSS
        $email = $this->xssProtection->sanitizeEmail($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $firstName = $this->xssProtection->sanitize($data['firstName'] ?? $data['first_name'] ?? '');
        $lastName = $this->xssProtection->sanitize($data['lastName'] ?? $data['last_name'] ?? '');

        if (!$email || empty($password) || empty($firstName) || empty($lastName)) {
            http_response_code(400);
            echo json_encode(['error' => 'Champs requis manquants'], JSON_UNESCAPED_UNICODE);
            return [];
        }

        try {
            $owner = $this->ownerService->register(
                $email,
                $password,
                $firstName,
                $lastName
            );

            http_response_code(201);
            echo json_encode([
                'id' => $owner->getOwnerId(),
                'email' => $owner->getEmail(),
                'firstName' => $owner->getFirstName(),
                'lastName' => $owner->getLastName(),
            ], JSON_UNESCAPED_UNICODE);

            return [
                'id' => $owner->getOwnerId(),
                'email' => $owner->getEmail(),
                'firstName' => $owner->getFirstName(),
                'lastName' => $owner->getLastName(),
            ];
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['error' => $this->xssProtection->sanitize($e->getMessage())], JSON_UNESCAPED_UNICODE);
            return [];
        } catch (\Exception $e) {
            error_log('Owner registration error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Une erreur est survenue lors de l\'inscription'], JSON_UNESCAPED_UNICODE);
            return [];
        }
    }

    public function login(array $data = []): ?array
    {
        header('Content-Type: application/json; charset=UTF-8');

        // Récupération des données (JSON ou POST ou array)
        if (empty($data)) {
            $input = file_get_contents('php://input');
            $jsonData = json_decode($input, true);
            $data = $jsonData ?: $_POST;
        }

        // Protection anti-XSS
        $email = $this->xssProtection->sanitizeEmail($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (!$email || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Champs requis manquants'], JSON_UNESCAPED_UNICODE);
            return null;
        }

        try {
            $owner = $this->ownerService->authenticate($email, $password);
            if ($owner) {
                http_response_code(200);
                echo json_encode([
                    'id' => $owner->getOwnerId(),
                    'email' => $owner->getEmail(),
                    'firstName' => $owner->getFirstName(),
                    'lastName' => $owner->getLastName(),
                ], JSON_UNESCAPED_UNICODE);

                return [
                    'id' => $owner->getOwnerId(),
                    'email' => $owner->getEmail(),
                    'firstName' => $owner->getFirstName(),
                    'lastName' => $owner->getLastName(),
                ];
            }

            http_response_code(401);
            echo json_encode(['error' => 'Identifiants invalides'], JSON_UNESCAPED_UNICODE);
            return null;
        } catch (\Exception $e) {
            error_log('Owner login error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Une erreur est survenue lors de la connexion'], JSON_UNESCAPED_UNICODE);
            return null;
        }
    }
}


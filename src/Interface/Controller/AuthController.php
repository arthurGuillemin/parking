<?php
namespace App\Interface\Controller;

use App\Application\UseCase\LoginUseCase;
use App\Domain\Security\XssProtectionService;

class AuthController
{
    private LoginUseCase $loginUseCase;
    private XssProtectionService $xssProtection;

    public function __construct(LoginUseCase $loginUseCase, XssProtectionService $xssProtection)
    {
        $this->loginUseCase = $loginUseCase;
        $this->xssProtection = $xssProtection;
    }

    public function loginForm(): void
    {
        header('Content-Type: text/html; charset=UTF-8');
        require __DIR__ . '/../../../templates/login_form.php';
    }

    public function login(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        // Récupération des données (JSON ou POST)
        $input = file_get_contents('php://input');
        $jsonData = json_decode($input, true);

        // Récupération et nettoyage des données
        $rawEmail = $jsonData['email'] ?? $_POST['email'] ?? '';
        $password = $jsonData['password'] ?? $_POST['password'] ?? '';

        // Protection anti-XSS sur l'email
        $email = $this->xssProtection->sanitizeEmail($rawEmail);

        if (!$email) {
            $this->sendErrorResponse(400, 'Format d\'email invalide');
            return;
        }

        if (!$this->isValidPassword($password)) {
            $this->sendErrorResponse(400, 'Le mot de passe doit contenir au moins 8 caractères');
            return;
        }

        try {
            $response = $this->loginUseCase->execute($email, $password);
            if ($response) {
                $this->handleSuccessfulLogin($response);
            } else {
                $this->handleFailedLogin();
            }
        } catch (\Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            $this->sendErrorResponse(500, 'Une erreur est survenue lors de la connexion');
        }
    }

    public function logout(): void
    {
        // Supprimer le cookie refresh token
        setcookie('refresh_token', '', [
            'expires' => time() - 3600,
            'httponly' => true,
            'secure' => ($_ENV['APP_ENV'] ?? 'development') === 'production',
            'samesite' => 'Lax',
            'path' => '/',
        ]);

        // Supprimer le cookie auth_token
        setcookie('auth_token', '', [
            'expires' => time() - 3600,
            'httponly' => true,
            'secure' => ($_ENV['APP_ENV'] ?? 'development') === 'production',
            'samesite' => 'Lax',
            'path' => '/',
        ]);

        // Supprimer le cookie owner_token
        setcookie('owner_token', '', [
            'expires' => time() - 3600,
            'httponly' => true,
            'secure' => ($_ENV['APP_ENV'] ?? 'development') === 'production',
            'samesite' => 'Lax',
            'path' => '/',
        ]);

        // Détruire la session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        header('Location: /login');
        exit;
    }

    private function isValidPassword(string $password): bool
    {
        return strlen($password) >= 8;
    }

    private function sendErrorResponse(int $code, string $message): void
    {
        http_response_code($code);
        // Protection anti-XSS sur le message d'erreur
        $safeMessage = $this->xssProtection->sanitize($message);
        echo json_encode(['error' => $safeMessage], JSON_UNESCAPED_UNICODE);
    }

    private function handleSuccessfulLogin($response): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['jwt_token'] = $response->token;
        $_SESSION['user_role'] = $response->role ?? 'user';

        // Configuration sécurisée du cookie refresh token
        $isSecure = ($_ENV['APP_ENV'] ?? 'development') === 'production';
        setcookie('refresh_token', $response->refreshToken, [
            'expires' => time() + \App\Domain\Service\JwtService::REFRESH_TOKEN_TTL,
            'httponly' => true,
            'secure' => $isSecure, // HTTPS uniquement en production
            'samesite' => 'Lax',
            'path' => '/',
        ]);

        // Configuration sécurisée du cookie access token (auth_token)
        setcookie('auth_token', $response->token, [
            'expires' => time() + \App\Domain\Service\JwtService::ACCESS_TOKEN_TTL,
            'httponly' => true,
            'secure' => $isSecure,
            'samesite' => 'Lax',
            'path' => '/',
        ]);

        http_response_code(200);
        echo json_encode([
            'token' => $response->token,
            'expires_in' => $response->expiresIn,
            'role' => $response->role ?? 'user',
            'firstName' => $response->firstName,
            'lastName' => $response->lastName
        ], JSON_UNESCAPED_UNICODE);
    }

    private function handleFailedLogin(): void
    {
        http_response_code(401);
        echo json_encode(['error' => 'Identifiants invalides'], JSON_UNESCAPED_UNICODE);
    }
}

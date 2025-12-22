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

    /**
     * Gère la connexion utilisateur
     */
    public function login(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        $credentials = $this->parseLoginInput();
        $email = $this->xssProtection->sanitizeEmail($credentials['email']);

        if (!$email) {
            $this->sendErrorResponse(400, 'Format d\'email invalide');
            return;
        }

        if (!$this->isValidPassword($credentials['password'])) {
            $this->sendErrorResponse(400, 'Le mot de passe doit contenir au moins 8 caractères');
            return;
        }

        try {
            $response = $this->loginUseCase->execute($email, $credentials['password']);
            $response ? $this->handleSuccessfulLogin($response) : $this->handleFailedLogin();
        } catch (\Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            $this->sendErrorResponse(500, 'Une erreur est survenue lors de la connexion');
        }
    }

    /**
     * Déconnexion utilisateur
     */
    public function logout(): void
    {
        $this->clearAuthCookies();
        $this->destroySession();

        header('Location: /login');
        exit;
    }

    /**
     * Parse les données de connexion
     */
    private function parseLoginInput(): array
    {
        $input = file_get_contents('php://input');
        $jsonData = json_decode($input, true) ?? [];

        return [
            'email' => $jsonData['email'] ?? $_POST['email'] ?? '',
            'password' => $jsonData['password'] ?? $_POST['password'] ?? '',
        ];
    }

    /**
     * Supprime tous les cookies d'authentification
     */
    private function clearAuthCookies(): void
    {
        $cookieNames = ['refresh_token', 'auth_token', 'owner_token'];
        foreach ($cookieNames as $name) {
            $this->clearCookie($name);
        }
    }

    /**
     * Supprime un cookie spécifique
     */
    private function clearCookie(string $name): void
    {
        setcookie($name, '', [
            'expires' => time() - 3600,
            'httponly' => true,
            'secure' => $this->isProduction(),
            'samesite' => 'Lax',
            'path' => '/',
        ]);
    }

    /**
     * Définit un cookie sécurisé
     */
    private function setSecureCookie(string $name, string $value, int $ttl): void
    {
        setcookie($name, $value, [
            'expires' => time() + $ttl,
            'httponly' => true,
            'secure' => $this->isProduction(),
            'samesite' => 'Lax',
            'path' => '/',
        ]);
    }

    /**
     * Vérifie si on est en production
     */
    private function isProduction(): bool
    {
        return ($_ENV['APP_ENV'] ?? 'development') === 'production';
    }

    /**
     * Détruit la session active
     */
    private function destroySession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    private function isValidPassword(string $password): bool
    {
        return strlen($password) >= 8;
    }

    private function sendErrorResponse(int $code, string $message): void
    {
        http_response_code($code);
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

        $this->setSecureCookie('refresh_token', $response->refreshToken, \App\Domain\Service\JwtService::REFRESH_TOKEN_TTL);
        $this->setSecureCookie('auth_token', $response->token, \App\Domain\Service\JwtService::ACCESS_TOKEN_TTL);

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

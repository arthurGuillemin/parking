<?php
namespace App\Interface\Controller;

use App\Application\UseCase\LoginUseCase;

class AuthController
{
    private LoginUseCase $loginUseCase;

    public function __construct(LoginUseCase $loginUseCase)
    {
        $this->loginUseCase = $loginUseCase;
    }

    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$this->isValidEmail($email)) {
            $this->sendErrorResponse(400, 'Format d\'email invalide');
            return;
        }
        if (!$this->isValidPassword($password)) {
            $this->sendErrorResponse(400, 'Le mot de passe doit contenir au moins 8 caractères');
            return;
        }

        $response = $this->loginUseCase->execute($email, $password);
        if ($response) {
            $this->handleSuccessfulLogin($response);
        } else {
            $this->handleFailedLogin();
        }
    }

    private function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function isValidPassword(string $password): bool
    {
        return strlen($password) >= 8;
    }

    private function sendErrorResponse(int $code, string $message): void
    {
        http_response_code($code);
        echo json_encode(['error' => $message]);
    }

   private function handleSuccessfulLogin($response): void
   {
       if (session_status() === PHP_SESSION_NONE) {
           session_start();
       }
       $_SESSION['jwt_token'] = $response->token;
       echo json_encode([
           'token' => $response->token,
           'expires_in' => $response->expiresIn
       ]);
   }

   private function handleFailedLogin(): void
   {
       http_response_code(401);
       echo json_encode(['error' => 'Invalid credentials']);
   }
}

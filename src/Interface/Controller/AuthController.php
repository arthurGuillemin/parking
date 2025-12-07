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

   public function login($tokenManager): void
   {
       $email = $_POST['email'] ?? '';
       $password = $_POST['password'] ?? '';

       $response = $this->loginUseCase->execute($email, $password);

       if ($response) {
           $this->handleSuccessfulLogin($response, $tokenManager);
       } else {
           $this->handleFailedLogin();
       }
   }

   private function handleSuccessfulLogin($response, $tokenManager): void
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

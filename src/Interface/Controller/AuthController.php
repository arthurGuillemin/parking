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
            session_start();
            $_SESSION['jwt_token'] = $response->token;
            // DEBUG : Affiche le jeton et le payload
            echo '<pre>JWT: ';
            print_r($response->token);
            echo '</pre>';
            $decoded = $tokenManager->decode($response->token);
            echo '<pre>Payload: ';
            print_r($decoded);
            echo '</pre>';
            echo json_encode([
                'token' => $response->token,
                'expires_in' => $response->expiresIn
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
        }
    }
}

<?php
namespace App\Interface\Presenter\Auth;

class LoginPresenter
{
    public function present($response): string
    {
        if ($response === null) {
            http_response_code(401);
            return json_encode(['error' => 'Invalid credentials']);
        }
        return json_encode([
            'token' => $response->token,
            'expires_in' => $response->expiresIn
        ]);
    }
}


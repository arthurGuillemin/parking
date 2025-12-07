<?php
namespace App\Interface\Controller;

use App\Domain\Service\JwtService;

class RefreshTokenController
{
    private JwtService $jwtService;
    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function refresh(): void
    {
        // Le refresh token doit être envoyé en cookie httpOnly ou dans le body
        $refreshToken = $_COOKIE['refresh_token'] ?? ($_POST['refresh_token'] ?? null);
        if (!$refreshToken) {
            http_response_code(401);
            echo json_encode(['error' => 'No refresh token']);
            return;
        }
        // La méthode decode() de JwtService vérifie l'expiration et la validité du token (exp, nbf, signature, etc.)
        $payload = $this->jwtService->decode($refreshToken);
        // Validation explicite du type de token
        if (!$payload || !isset($payload['type']) || $payload['type'] !== 'refresh') {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid refresh token']);
            return;
        }
        // À ce stade, le token est valide, non expiré, et de type 'refresh'.
        $accessPayload = $payload;
        unset($accessPayload['exp'], $accessPayload['iat'], $accessPayload['type']);
        $accessToken = $this->jwtService->generate(array_merge($accessPayload, ['type' => 'access']));
        echo json_encode(['token' => $accessToken]);
    }
}

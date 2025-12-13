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
        header('Content-Type: application/json; charset=UTF-8');
        
        // Le refresh token doit être envoyé en cookie httpOnly (priorité) ou dans le body
        $refreshToken = $_COOKIE['refresh_token'] ?? ($_POST['refresh_token'] ?? $_GET['refresh_token'] ?? null);
        
        if (!$refreshToken) {
            http_response_code(401);
            echo json_encode(['error' => 'Aucun refresh token fourni'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // La méthode decode() de JwtService vérifie l'expiration et la validité du token (exp, nbf, signature, etc.)
        $payload = $this->jwtService->decode($refreshToken);
        
        // Validation explicite du type de token
        if (!$payload || !isset($payload['type']) || $payload['type'] !== 'refresh') {
            http_response_code(401);
            echo json_encode(['error' => 'Refresh token invalide ou expiré'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // À ce stade, le token est valide, non expiré, et de type 'refresh'.
        // Générer un nouveau access token avec les mêmes informations utilisateur
        $accessPayload = $payload;
        unset($accessPayload['exp'], $accessPayload['iat'], $accessPayload['nbf'], $accessPayload['type']);
        
        $accessToken = $this->jwtService->generate(array_merge($accessPayload, ['type' => 'access']));
        
        http_response_code(200);
        echo json_encode([
            'token' => $accessToken,
            'expires_in' => JwtService::ACCESS_TOKEN_TTL,
            'role' => $payload['role'] ?? 'user'
        ], JSON_UNESCAPED_UNICODE);
    }
}

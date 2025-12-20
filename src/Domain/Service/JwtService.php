<?php

namespace App\Domain\Service;

use App\Domain\Auth\TokenGeneratorInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService implements TokenGeneratorInterface
{

    private string $secretKey;
    private string $algorithm = 'HS256';

    public const ACCESS_TOKEN_TTL = 600; // 10 minutes
    public const REFRESH_TOKEN_TTL = 604800;

    public function __construct()
    {
        $secret = $_ENV['JWT_SECRET_KEY'] ?? $_SERVER['JWT_SECRET_KEY'] ?? getenv('JWT_SECRET_KEY');
        if (!is_string($secret) || trim($secret) === '') {
            throw new \RuntimeException("La variable d'environnement JWT_SECRET_KEY doit être définie avec une valeur non vide.");
        }
        $this->secretKey = $secret;
    }

    public function generate(array $payload): string
    {
        $now = time();
        $payload['iat'] = $now; // Issued at
        $payload['nbf'] = $now; // Not before (token valide immédiatement)

        if (!isset($payload['exp'])) {
            $payload['exp'] = ($payload['type'] ?? 'access') === 'refresh'
                ? $now + self::REFRESH_TOKEN_TTL
                : $now + self::ACCESS_TOKEN_TTL;
        }

        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    public function decode(string $token): ?array
    {
        try {
            return (array) JWT::decode($token, new Key($this->secretKey, $this->algorithm));
        } catch (\Firebase\JWT\ExpiredException $e) {
            error_log('JWT expired: ' . $e->getMessage());
            return null;
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            error_log('JWT signature invalid: ' . $e->getMessage());
            return null;
        } catch (\Firebase\JWT\BeforeValidException $e) {
            error_log('JWT not valid yet: ' . $e->getMessage());
            return null;
        } catch (\UnexpectedValueException $e) {
            error_log('JWT decode error: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            error_log('JWT unknown error: ' . $e->getMessage());
            return null;
        }
    }
    public function validateToken(string $token): ?string
    {
        $decoded = $this->decode($token);
        // Check for 'user_id' as that's what LoginUseCase uses
        if (!$decoded || !isset($decoded['user_id'])) {
            return null;
        }
        return $decoded['user_id'];
    }
}

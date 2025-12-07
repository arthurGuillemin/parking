<?php

namespace App\Domain\Service;

use App\Domain\Auth\TokenGeneratorInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService implements TokenGeneratorInterface {

    private string $secretKey = '';
    private string $algorithm = 'HS256';

    public const ACCESS_TOKEN_TTL = 3600;
    public const REFRESH_TOKEN_TTL = 604800;

    public function __construct()
    {
        $secret = getenv('JWT_SECRET_KEY');
        if (!is_string($secret) || trim($secret) === '') {
            throw new \RuntimeException("L'environnement variable JWT_SECRET_KEY doit être définie avec une valeur non vide.");
        }
        $this->secretKey = $secret;
    }

    public function generate(array $payload): string
    {
        $payload['iat'] = time();
        if (!isset($payload['exp'])) {
            $payload['exp'] = $payload['type'] === 'refresh' ? time() + self::REFRESH_TOKEN_TTL : time() + self::ACCESS_TOKEN_TTL;
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
}

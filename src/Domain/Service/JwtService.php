<?php

namespace App\Domain\Service;

require_once 'vendor/autoload.php';

use App\Domain\Auth\TokenGeneratorInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService implements TokenGeneratorInterface {

    private string $secretKey = '';
    private string $algorithm = 'HS256';

    public function __construct()
    {
        $this->secretKey = getenv('JWT_SECRET_KEY');
    }

    public function generate(array $payload): string
    {
        $payload['iat'] = time();
        if (!isset($payload['exp'])) {
            $payload['exp'] = $payload['type'] === 'refresh' ? time() + 604800 : time() + 3600;
        }
        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    public function decode(string $token): ?array
    {
        try {
            return (array) JWT::decode($token, new Key($this->secretKey, $this->algorithm));
        } catch (\Exception $e) {
            return null;
        }
    }
}

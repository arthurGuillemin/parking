<?php
namespace Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use App\Domain\Service\JwtService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtServiceTest extends TestCase
{
    private string $secret = 'this_is_a_test_secret_key_that_is_at_least_32_characters_long_for_hs256';

    protected function setUp(): void
    {
        parent::setUp();
        putenv('JWT_SECRET_KEY=' . $this->secret);
    }

    public function testGenerateAndDecodeValidToken()
    {
        $service = new JwtService();
        $payload = ['user_id' => 123, 'role' => 'user', 'type' => 'access'];
        $token = $service->generate($payload);
        $decoded = $service->decode($token);
        $this->assertIsArray($decoded);
        $this->assertEquals(123, $decoded['user_id']);
        $this->assertEquals('user', $decoded['role']);
        $this->assertEquals('access', $decoded['type']);
        $this->assertArrayHasKey('iat', $decoded);
        $this->assertArrayHasKey('exp', $decoded);
    }

    public function testDecodeWithInvalidTokenReturnsNull()
    {
        $service = new JwtService();
        $invalidToken = 'invalid.token.value';
        $this->assertNull($service->decode($invalidToken));
    }

    public function testDecodeWithExpiredTokenReturnsNull()
    {
        $service = new JwtService();
        $payload = [
            'user_id' => 1,
            'role' => 'user',
            'type' => 'access',
            'iat' => time() - 7200,
            'exp' => time() - 3600,
        ];
        $token = JWT::encode($payload, $this->secret, 'HS256');
        $this->assertNull($service->decode($token));
    }

    public function testGenerateWithMissingSecretKeyThrowsOrReturnsInvalidToken()
    {
        putenv('JWT_SECRET_KEY');
        unset($_ENV['JWT_SECRET_KEY']);
        unset($_SERVER['JWT_SECRET_KEY']);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("La variable d'environnement JWT_SECRET_KEY doit être définie avec une valeur non vide.");
        $service = new JwtService();
        $payload = ['user_id' => 1, 'role' => 'user', 'type' => 'access'];
        $service->generate($payload);
    }
}

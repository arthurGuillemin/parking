<?php
namespace Unit\Interface\Controller;

use PHPUnit\Framework\TestCase;
use App\Interface\Controller\RefreshTokenController;
use App\Domain\Service\JwtService;

class RefreshTokenControllerTest extends TestCase
{
    public function testRefreshReturnsNewAccessToken()
    {
        $jwtService = $this->createMock(JwtService::class);
        $payload = [
            'user_id' => 1,
            'email' => 'test@example.com',
            'role' => 'user',
            'type' => 'refresh',
        ];
        $_COOKIE['refresh_token'] = 'refresh.jwt.token';
        $jwtService->method('decode')->willReturn($payload);
        $jwtService->method('generate')->willReturn('new.access.token');
        $controller = new RefreshTokenController($jwtService);
        ob_start();
        $controller->refresh();
        $output = ob_get_clean();
        $this->assertStringContainsString('new.access.token', $output);
    }
    public function testRefreshFailsWithInvalidToken()
    {
        $jwtService = $this->createMock(JwtService::class);
        $_COOKIE['refresh_token'] = 'invalid.token';
        $jwtService->method('decode')->willReturn(null);
        $controller = new RefreshTokenController($jwtService);
        ob_start();
        $controller->refresh();
        $output = ob_get_clean();
        $this->assertStringContainsString('Invalid refresh token', $output);
    }
}


<?php
namespace Unit\Interface\Middleware;

use PHPUnit\Framework\TestCase;
use App\Interface\Middleware\AuthenticationMiddleware;
use App\Domain\Service\JwtService;

class AuthenticationMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $_SESSION = [];
        $_SERVER = [];
    }

    public function testValidBearerToken()
    {
        $jwtService = $this->createMock(JwtService::class);
        $jwtService->method('decode')->willReturn(['user_id' => '1']);
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer valid.token';
        $middleware = new AuthenticationMiddleware($jwtService);
        $this->assertTrue($middleware->handle());
    }

    public function testInvalidBearerToken()
    {
        $jwtService = $this->createMock(JwtService::class);
        $jwtService->method('decode')->willReturn(null);
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer invalid.token';
        $middleware = new AuthenticationMiddleware($jwtService);
        ob_start();
        $result = $middleware->handle();
        $output = ob_get_clean();
        $this->assertFalse($result);
        $this->assertStringContainsString('Unauthorized', $output);
    }

    public function testValidSessionToken()
    {
        $jwtService = $this->createMock(JwtService::class);
        $jwtService->method('decode')->willReturn(['user_id' => '1']);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['jwt_token'] = 'valid.session.token';
        $middleware = new AuthenticationMiddleware($jwtService);
        $this->assertTrue($middleware->handle());
    }

    public function testInvalidSessionToken()
    {
        $jwtService = $this->createMock(JwtService::class);
        $jwtService->method('decode')->willReturn(null);
        $_SESSION['jwt_token'] = 'invalid.session.token';
        $middleware = new AuthenticationMiddleware($jwtService);
        ob_start();
        $result = $middleware->handle();
        $output = ob_get_clean();
        $this->assertFalse($result);
        $this->assertStringContainsString('Unauthorized', $output);
    }

    public function testMissingToken()
    {
        $jwtService = $this->createMock(JwtService::class);
        $jwtService->method('decode')->willReturn(null);
        $middleware = new AuthenticationMiddleware($jwtService);
        ob_start();
        $result = $middleware->handle();
        $output = ob_get_clean();
        $this->assertFalse($result);
        $this->assertStringContainsString('Unauthorized', $output);
    }
}

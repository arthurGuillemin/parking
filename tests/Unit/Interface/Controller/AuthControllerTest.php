<?php
namespace Unit\Interface\Controller;

use PHPUnit\Framework\TestCase;
use App\Interface\Controller\AuthController;
use App\Application\UseCase\LoginUseCase;
use App\Application\DTO\LoginResponse;
use App\Domain\Security\XssProtectionService;

class AuthControllerTest extends TestCase
{
    private XssProtectionService $xssProtection;

    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $_SESSION = [];
        $this->xssProtection = new XssProtectionService();
    }

    public function testLoginSuccess()
    {
        $mockUseCase = $this->createMock(LoginUseCase::class);
        $mockResponse = new LoginResponse('jwt.token.value', 3600, 'user');
        $mockUseCase->method('execute')->willReturn($mockResponse);
        $_POST['email'] = 'test@example.com';
        $_POST['password'] = 'password123';
        ob_start();
        $controller = new AuthController($mockUseCase, $this->xssProtection);
        $controller->login();
        $output = ob_get_clean();
        $this->assertStringContainsString('jwt.token.value', $output);
        $this->assertStringContainsString('token', $output);
        $this->assertStringContainsString('expires_in', $output);
        $this->assertEquals('jwt.token.value', $_SESSION['jwt_token']);
    }

    public function testLoginFailure()
    {
        $mockUseCase = $this->createMock(LoginUseCase::class);
        $mockUseCase->method('execute')->willReturn(null);
        $_POST['email'] = 'test@example.com';
        $_POST['password'] = 'wrongpassword';
        ob_start();
        $controller = new AuthController($mockUseCase, $this->xssProtection);
        $controller->login();
        $output = ob_get_clean();
        $this->assertStringContainsString('Identifiants invalides', $output);
        $this->assertArrayNotHasKey('jwt_token', $_SESSION);
    }
}

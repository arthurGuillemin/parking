<?php
namespace Unit\Interface\Controller;

use PHPUnit\Framework\TestCase;
use App\Interface\Controller\AuthController;
use App\Application\UseCase\LoginUseCase;
use App\Application\DTO\LoginResponse;

class AuthControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $_SESSION = [];
    }

    public function testLoginSuccess()
    {
        $mockUseCase = $this->createMock(LoginUseCase::class);
        $mockTokenManager = new class {
            public function decode($token) {
                return ['user_id' => 1, 'role' => 'user'];
            }
        };
        $mockResponse = new LoginResponse('jwt.token.value', 3600);
        $mockUseCase->method('execute')->willReturn($mockResponse);
        $_POST['email'] = 'test@example.com';
        $_POST['password'] = 'password';
        ob_start();
        $controller = new AuthController($mockUseCase);
        $controller->login($mockTokenManager);
        $output = ob_get_clean();
        $this->assertStringContainsString('jwt.token.value', $output);
        $this->assertStringContainsString('user_id', $output);
        $this->assertStringContainsString('token', $output);
        $this->assertStringContainsString('expires_in', $output);
        $this->assertEquals('jwt.token.value', $_SESSION['jwt_token']);
    }

    public function testLoginFailure()
    {
        $mockUseCase = $this->createMock(LoginUseCase::class);
        $mockTokenManager = new class {
            public function decode($token) {
                return null;
            }
        };
        $mockUseCase->method('execute')->willReturn(null);
        $_POST['email'] = 'test@example.com';
        $_POST['password'] = 'wrongpassword';
        ob_start();
        $controller = new AuthController($mockUseCase);
        $controller->login($mockTokenManager);
        $output = ob_get_clean();
        $this->assertStringContainsString('Invalid credentials', $output);
        $this->assertArrayNotHasKey('jwt_token', $_SESSION);
    }
}

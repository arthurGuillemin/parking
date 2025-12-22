<?php
namespace Unit\Interface\Controller;

use PHPUnit\Framework\TestCase;
use App\Interface\Controller\OwnerController;
use App\Domain\Service\OwnerService;
use App\Domain\Entity\Owner;
use App\Domain\Security\XssProtectionService;

class OwnerControllerTest extends TestCase
{
    private XssProtectionService $xssProtection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->xssProtection = new XssProtectionService();
    }

    public function testRegisterReturnsArray()
    {
        $mockService = $this->createMock(OwnerService::class);
        $mockJwt = $this->createMock(\App\Domain\Service\JwtService::class);
        $mockOwner = $this->createMock(Owner::class);
        $mockOwner->method('getOwnerId')->willReturn('uuid-1');
        $mockOwner->method('getEmail')->willReturn('test@example.com');
        $mockOwner->method('getFirstName')->willReturn('James');
        $mockOwner->method('getLastName')->willReturn('Lebron');
        $mockService->method('register')->willReturn($mockOwner);

        $this->xssProtection->sanitizeEmail('test@example.com'); // Call real method on real object if possible, or mock it? 
        // We use real XssProtectionService in setUp.

        ob_start();
        $controller = new OwnerController($mockService, $this->xssProtection, $mockJwt);
        $data = [
            'email' => 'test@example.com',
            'password' => 'pass',
            'firstName' => 'James',
            'lastName' => 'Lebron'
        ];
        $controller->register($data);
        $output = ob_get_clean();

        $this->assertStringContainsString('uuid-1', $output);
        $this->assertStringContainsString('test@example.com', $output);
    }

    public function testRegisterReturnsErrorOnMissingFields()
    {
        $mockJwt = $this->createMock(\App\Domain\Service\JwtService::class);
        ob_start();
        $controller = new OwnerController($this->createMock(OwnerService::class), $this->xssProtection, $mockJwt);
        $controller->register(['email' => 'test@example.com']); // Missing other fields
        $output = ob_get_clean();
        $this->assertStringContainsString('error', $output);
    }

    public function testLoginReturnsArray()
    {
        $mockService = $this->createMock(OwnerService::class);
        $mockJwt = $this->createMock(\App\Domain\Service\JwtService::class);

        // Mock LoginResponse DTO
        $loginResponse = new \App\Application\DTO\LoginResponse(
            'token_abc',
            'refresh_xyz',
            3600,
            'owner'
        );

        $mockService->method('authenticate')->willReturn($loginResponse);

        ob_start();
        $controller = new OwnerController($mockService, $this->xssProtection, $mockJwt);
        $data = [
            'email' => 'test@example.com',
            'password' => 'pass'
        ];
        $controller->login($data);
        $output = ob_get_clean();
        $this->assertStringContainsString('token_abc', $output);
    }

    public function testLoginReturnsNull()
    {
        $mockService = $this->createMock(OwnerService::class);
        $mockJwt = $this->createMock(\App\Domain\Service\JwtService::class);
        $mockService->method('authenticate')->willReturn(null);
        ob_start();
        $controller = new OwnerController($mockService, $this->xssProtection, $mockJwt);
        $data = [
            'email' => 'test@example.com',
            'password' => 'pass'
        ];
        $controller->login($data);
        $output = ob_get_clean();
        $this->assertStringContainsString('error', $output);
    }

    public function testLoginReturnsErrorOnMissingFields()
    {
        $mockJwt = $this->createMock(\App\Domain\Service\JwtService::class);
        ob_start();
        $controller = new OwnerController($this->createMock(OwnerService::class), $this->xssProtection, $mockJwt);
        $controller->login(['email' => 'test@example.com']);
        $output = ob_get_clean();
        $this->assertStringContainsString('error', $output);
    }
}

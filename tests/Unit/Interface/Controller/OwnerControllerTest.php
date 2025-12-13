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
        $mockOwner = $this->createMock(Owner::class);
        $mockOwner->method('getOwnerId')->willReturn('uuid-1');
        $mockOwner->method('getEmail')->willReturn('test@example.com');
        $mockOwner->method('getFirstName')->willReturn('James');
        $mockOwner->method('getLastName')->willReturn('Lebron');
        $mockService->method('register')->willReturn($mockOwner);
        ob_start();
        $controller = new OwnerController($mockService, $this->xssProtection);
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
        ob_start();
        $controller = new OwnerController($this->createMock(OwnerService::class), $this->xssProtection);
        $controller->register(['email' => 'test@example.com']);
        $output = ob_get_clean();
        $this->assertStringContainsString('error', $output);
    }
    
    public function testLoginReturnsArray()
    {
        $mockService = $this->createMock(OwnerService::class);
        $mockOwner = $this->createMock(Owner::class);
        $mockOwner->method('getOwnerId')->willReturn('uuid-1');
        $mockOwner->method('getEmail')->willReturn('test@example.com');
        $mockOwner->method('getFirstName')->willReturn('James');
        $mockOwner->method('getLastName')->willReturn('Lebron');
        $mockService->method('authenticate')->willReturn($mockOwner);
        ob_start();
        $controller = new OwnerController($mockService, $this->xssProtection);
        $data = [
            'email' => 'test@example.com',
            'password' => 'pass'
        ];
        $controller->login($data);
        $output = ob_get_clean();
        $this->assertStringContainsString('uuid-1', $output);
        $this->assertStringContainsString('test@example.com', $output);
    }
    
    public function testLoginReturnsNull()
    {
        $mockService = $this->createMock(OwnerService::class);
        $mockService->method('authenticate')->willReturn(null);
        ob_start();
        $controller = new OwnerController($mockService, $this->xssProtection);
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
        ob_start();
        $controller = new OwnerController($this->createMock(OwnerService::class), $this->xssProtection);
        $controller->login(['email' => 'test@example.com']);
        $output = ob_get_clean();
        $this->assertStringContainsString('error', $output);
    }
}

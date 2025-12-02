<?php
namespace Unit\Interface\Controller;

use PHPUnit\Framework\TestCase;
use App\Interface\Controller\OwnerController;
use App\Domain\Service\OwnerService;
use App\Domain\Entity\Owner;

class OwnerControllerTest extends TestCase
{
    public function testRegisterReturnsArray()
    {
        $mockService = $this->createMock(OwnerService::class);
        $mockOwner = $this->createMock(Owner::class);
        $mockOwner->method('getOwnerId')->willReturn('uuid-1');
        $mockOwner->method('getEmail')->willReturn('test@example.com');
        $mockOwner->method('getFirstName')->willReturn('James');
        $mockOwner->method('getLastName')->willReturn('Lebron');
        $mockService->method('register')->willReturn($mockOwner);
        $controller = new OwnerController($mockService);
        $data = [
            'email' => 'test@example.com',
            'password' => 'pass',
            'firstName' => 'James',
            'lastName' => 'Lebron'
        ];
        $result = $controller->register($data);
        $this->assertEquals([
            'id' => 'uuid-1',
            'email' => 'test@example.com',
            'firstName' => 'James',
            'lastName' => 'Lebron',
        ], $result);
    }
    public function testRegisterThrowsOnMissingFields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $controller = new OwnerController($this->createMock(OwnerService::class));
        $controller->register(['email' => 'test@example.com']);
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
        $controller = new OwnerController($mockService);
        $data = [
            'email' => 'test@example.com',
            'password' => 'pass'
        ];
        $result = $controller->login($data);
        $this->assertEquals([
            'id' => 'uuid-1',
            'email' => 'test@example.com',
            'firstName' => 'James',
            'lastName' => 'Lebron',
        ], $result);
    }
    public function testLoginReturnsNull()
    {
        $mockService = $this->createMock(OwnerService::class);
        $mockService->method('authenticate')->willReturn(null);
        $controller = new OwnerController($mockService);
        $data = [
            'email' => 'test@example.com',
            'password' => 'pass'
        ];
        $result = $controller->login($data);
        $this->assertNull($result);
    }
    public function testLoginThrowsOnMissingFields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $controller = new OwnerController($this->createMock(OwnerService::class));
        $controller->login(['email' => 'test@example.com']);
    }
}

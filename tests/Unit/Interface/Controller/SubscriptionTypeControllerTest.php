<?php
namespace Unit\Interface\Controller;

use PHPUnit\Framework\TestCase;
use App\Interface\Controller\SubscriptionTypeController;
use App\Domain\Service\SubscriptionTypeService;
use App\Domain\Entity\SubscriptionType;

class SubscriptionTypeControllerTest extends TestCase
{
    public function testAddReturnsArray()
    {
        $mockService = $this->createMock(SubscriptionTypeService::class);
        $mockType = $this->createMock(SubscriptionType::class);
        $mockType->method('getSubscriptionTypeId')->willReturn(1);
        $mockType->method('getName')->willReturn('Premium');
        $mockType->method('getDescription')->willReturn('desc');
        $mockService->method('addSubscriptionType')->willReturn($mockType);
        $controller = new SubscriptionTypeController($mockService);
        $data = [
            'parkingId' => 2,
            'name' => 'Premium',
            'description' => 'desc'
        ];
        $result = $controller->add($data);
        $this->assertEquals([
            'id' => 1,
            'name' => 'Premium',
            'description' => 'desc',
        ], $result);
    }
    public function testAddThrowsOnMissingFields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $controller = new SubscriptionTypeController($this->createMock(SubscriptionTypeService::class));
        $controller->add(['parkingId' => 2]);
    }
}


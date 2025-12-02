<?php
namespace Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use App\Domain\Service\SubscriptionTypeService;
use App\Domain\Repository\SubscriptionTypeRepositoryInterface;
use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeUseCase;
use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeRequest;
use App\Domain\Entity\SubscriptionType;

class SubscriptionTypeServiceTest extends TestCase
{
    public function testConstructor()
    {
        $repo = $this->createMock(SubscriptionTypeRepositoryInterface::class);
        $service = new SubscriptionTypeService($repo);
        $this->assertInstanceOf(SubscriptionTypeService::class, $service);
    }

    public function testAddSubscriptionTypeReturnsSubscriptionType()
    {
        $repo = $this->createMock(SubscriptionTypeRepositoryInterface::class);
        $service = new SubscriptionTypeService($repo);
        $mockUseCase = $this->getMockBuilder(AddSubscriptionTypeUseCase::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['execute'])
            ->getMock();
        $mockSubscriptionType = $this->createMock(SubscriptionType::class);
        $mockUseCase->method('execute')->willReturn($mockSubscriptionType);
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('addSubscriptionTypeUseCase');
        $property->setAccessible(true);
        $property->setValue($service, $mockUseCase);
        $mockRequest = $this->createMock(AddSubscriptionTypeRequest::class);
        $result = $service->addSubscriptionType($mockRequest);
        $this->assertInstanceOf(SubscriptionType::class, $result);
    }
}


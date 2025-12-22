<?php
namespace Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use App\Domain\Service\SubscriptionTypeService;
use App\Domain\Repository\SubscriptionTypeRepositoryInterface;
use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeUseCase;
use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeResponse;
use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeRequest;

class SubscriptionTypeServiceTest extends TestCase
{
    public function testConstructor()
    {
        $repo = $this->createStub(SubscriptionTypeRepositoryInterface::class);
        $service = new SubscriptionTypeService($repo);
        $this->assertInstanceOf(SubscriptionTypeService::class, $service);
    }

    public function testAddSubscriptionTypeReturnsSubscriptionType()
    {
        $repo = $this->createStub(SubscriptionTypeRepositoryInterface::class);
        $service = new SubscriptionTypeService($repo);
        $mockUseCase = $this->getMockBuilder(AddSubscriptionTypeUseCase::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['execute'])
            ->getMock();
        $mockResponse = new AddSubscriptionTypeResponse(1, 2, 'Premium', 'desc', 99.99);
        $mockUseCase->method('execute')->willReturn($mockResponse);
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('addSubscriptionTypeUseCase');
        $property->setValue($service, $mockUseCase);
        $mockRequest = $this->createStub(AddSubscriptionTypeRequest::class);
        $result = $service->addSubscriptionType($mockRequest);
        $this->assertInstanceOf(AddSubscriptionTypeResponse::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals(2, $result->parkingId);
        $this->assertEquals('Premium', $result->name);
        $this->assertEquals('desc', $result->description);
        $this->assertEquals(49.99, $result->monthlyPrice);
    }
}

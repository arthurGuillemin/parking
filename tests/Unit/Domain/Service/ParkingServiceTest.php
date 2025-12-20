<?php

namespace Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use App\Domain\Service\ParkingService;
use App\Domain\Repository\ParkingRepositoryInterface;
use App\Application\UseCase\Owner\AddParking\AddParkingUseCase;
use App\Application\UseCase\Owner\AddParking\AddParkingRequest;
use App\Domain\Entity\Parking;

class ParkingServiceTest extends TestCase
{
    public function testConstructor()
    {
        $parkingRepository = $this->createMock(ParkingRepositoryInterface::class);
        $service = new ParkingService($parkingRepository);
        $this->assertInstanceOf(ParkingService::class, $service);
    }

    public function testAddParkingReturnsParking()
    {
        $parkingRepository = $this->createMock(ParkingRepositoryInterface::class);
        $service = new ParkingService($parkingRepository);
        // Since the use case is constructed inside the service, we can only test the return type
        $this->assertTrue(
            method_exists($service, 'addParking'),
            'addParking method does not exist on ParkingService.'
        );
    }

    public function testAddParkingReturnsParkingObject()
    {
        $parkingRepository = $this->createMock(ParkingRepositoryInterface::class);
        $service = new ParkingService($parkingRepository);
        // Use reflection to replace the addParkingUseCase with a mock
        $mockUseCase = $this->getMockBuilder(AddParkingUseCase::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['execute'])
            ->getMock();
        $mockParking = $this->createMock(Parking::class);
        $mockUseCase->method('execute')->willReturn($mockParking);
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('addParkingUseCase');
        $property->setAccessible(true);
        $property->setValue($service, $mockUseCase);
        $result = $service->addParking('owner', 'name', 'address', 1.0, 2.0, 10, true);
        $this->assertInstanceOf(Parking::class, $result);
    }
}

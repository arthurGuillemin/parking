<?php
namespace Unit\Interface\Controller;

use PHPUnit\Framework\TestCase;
use App\Interface\Controller\ParkingAvailabilityController;
use App\Domain\Service\ParkingAvailabilityService;
use App\Application\UseCase\Owner\GetAvailableSpots\GetAvailableSpotsRequest;

class ParkingAvailabilityControllerTest extends TestCase
{
    public function testGetAvailableSpotsReturnsArray()
    {
        $mockService = $this->createMock(ParkingAvailabilityService::class);
        $mockService->method('getAvailableSpots')->willReturn(5);
        $controller = new ParkingAvailabilityController($mockService);
        $data = ['parkingId' => 1, 'at' => '2025-11-29T10:00:00+00:00'];
        $result = $controller->getAvailableSpots($data);
        $this->assertEquals([
            'parkingId' => 1,
            'at' => '2025-11-29T10:00:00+00:00',
            'availableSpots' => 5
        ], $result);
    }
    public function testGetAvailableSpotsThrowsOnMissingFields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $controller = new ParkingAvailabilityController($this->createMock(ParkingAvailabilityService::class));
        $controller->getAvailableSpots(['parkingId' => 1]);
    }
}


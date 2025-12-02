<?php
namespace Unit\Interface\Controller;

use PHPUnit\Framework\TestCase;
use App\Interface\Controller\ParkingController;
use App\Domain\Service\ParkingService;
use App\Domain\Entity\Parking;

class ParkingControllerTest extends TestCase
{
    public function testAddReturnsArray()
    {
        $mockService = $this->createMock(ParkingService::class);
        $mockParking = $this->createMock(Parking::class);
        $mockParking->method('getParkingId')->willReturn(1);
        $mockParking->method('getOwnerId')->willReturn('owner');
        $mockParking->method('getName')->willReturn('name');
        $mockParking->method('getAddress')->willReturn('address');
        $mockParking->method('getLatitude')->willReturn(1.0);
        $mockParking->method('getLongitude')->willReturn(2.0);
        $mockParking->method('getTotalCapacity')->willReturn(10);
        $mockParking->method('isOpen24_7')->willReturn(true);
        $mockService->method('addParking')->willReturn($mockParking);
        $controller = new ParkingController($mockService);
        $data = [
            'ownerId' => 'owner',
            'name' => 'name',
            'address' => 'address',
            'latitude' => 1.0,
            'longitude' => 2.0,
            'totalCapacity' => 10,
            'open_24_7' => true
        ];
        $result = $controller->add($data);
        $this->assertEquals([
            'id' => 1,
            'ownerId' => 'owner',
            'name' => 'name',
            'address' => 'address',
            'latitude' => 1.0,
            'longitude' => 2.0,
            'totalCapacity' => 10,
            'open_24_7' => true,
        ], $result);
    }
    public function testAddThrowsOnMissingFields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $controller = new ParkingController($this->createMock(ParkingService::class));
        $controller->add(['ownerId' => 'owner']);
    }
}


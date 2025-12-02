<?php
namespace Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\Parking;
use App\Domain\Service\ParkingAvailabilityService;
use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Application\UseCase\Owner\GetAvailableSpots\GetAvailableSpotsRequest;

class ParkingAvailabilityServiceTest extends TestCase
{
    public function testGetAvailableSpotsDelegatesToUseCase()
    {
        $parkingRepo = $this->createMock(ParkingRepositoryInterface::class);
        $sessionRepo = $this->createMock(ParkingSessionRepositoryInterface::class);
        $parking = $this->createMock(Parking::class);
        $parking->method('getTotalCapacity')->willReturn(10);
        $parkingRepo->method('findById')->willReturn($parking);
        $service = new ParkingAvailabilityService($parkingRepo, $sessionRepo);
        $request = new GetAvailableSpotsRequest(1, new \DateTimeImmutable());
        $result = $service->getAvailableSpots($request);
        $this->assertIsInt($result);
    }
}

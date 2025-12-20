<?php
namespace Unit\Application\UseCase\Parking;

use App\Application\DTO\Parking\CheckParkingAvailability\CheckParkingAvailabilityRequest;
use App\Application\DTO\Parking\CheckParkingAvailability\CheckParkingAvailabilityResponse;
use App\Application\UseCase\Parking\CheckParkingAvailability\CheckParkingAvailabilityUseCase;
use App\Domain\Entity\Parking;
use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Service\ParkingAvailabilityService;
use PHPUnit\Framework\TestCase;

class CheckParkingAvailabilityUseCaseTest extends TestCase
{
    public function testReturnsFalseIfParkingNotFound()
    {
        $parkingRepo = $this->createMock(ParkingRepositoryInterface::class);
        $parkingRepo->method('findById')->willReturn(null);
        $availabilityService = $this->createMock(ParkingAvailabilityService::class);
        $useCase = new CheckParkingAvailabilityUseCase($parkingRepo, $availabilityService);
        $request = new CheckParkingAvailabilityRequest(99, new \DateTimeImmutable('2025-12-05 10:00:00'));
        $response = $useCase->execute($request);
        $this->assertInstanceOf(CheckParkingAvailabilityResponse::class, $response);
        $this->assertFalse($response->available);
        $this->assertEquals('Parking non disponible', $response->message);
    }

    public function testReturnsTrueIfParkingAvailable()
    {
        $parkingRepo = $this->createMock(ParkingRepositoryInterface::class);
        $parking = $this->createMock(Parking::class);
        $parkingRepo->method('findById')->willReturn($parking);
        $availabilityService = $this->createMock(ParkingAvailabilityService::class);
        $availabilityService->method('isAvailable')->willReturn(true);
        $useCase = new CheckParkingAvailabilityUseCase($parkingRepo, $availabilityService);
        $request = new CheckParkingAvailabilityRequest(1, new \DateTimeImmutable('2025-12-05 10:00:00'));
        $response = $useCase->execute($request);
        $this->assertInstanceOf(CheckParkingAvailabilityResponse::class, $response);
        $this->assertTrue($response->available);
        $this->assertNull($response->message);
    }

    public function testReturnsFalseIfParkingNotAvailable()
    {
        $parkingRepo = $this->createMock(ParkingRepositoryInterface::class);
        $parking = $this->createMock(Parking::class);
        $parkingRepo->method('findById')->willReturn($parking);
        $availabilityService = $this->createMock(ParkingAvailabilityService::class);
        $availabilityService->method('isAvailable')->willReturn(false);
        $useCase = new CheckParkingAvailabilityUseCase($parkingRepo, $availabilityService);
        $request = new CheckParkingAvailabilityRequest(1, new \DateTimeImmutable('2025-12-05 10:00:00'));
        $response = $useCase->execute($request);
        $this->assertInstanceOf(CheckParkingAvailabilityResponse::class, $response);
        $this->assertFalse($response->available);
        $this->assertNull($response->message);
    }
}


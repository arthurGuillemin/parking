<?php
namespace Unit\Application\UseCase\Owner;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\Owner\GetAvailableSpots\GetAvailableSpotsUseCase;
use App\Application\UseCase\Owner\GetAvailableSpots\GetAvailableSpotsRequest;
use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Entity\Parking;
use App\Domain\Entity\ParkingSession;

class GetAvailableSpotsUseCaseTest extends TestCase
{
    public function testExecuteReturnsAvailableSpots()
    {
        $parkingRepo = $this->createMock(ParkingRepositoryInterface::class);
        $sessionRepo = $this->createMock(ParkingSessionRepositoryInterface::class);
        $parking = $this->createMock(Parking::class);
        $parking->method('getTotalCapacity')->willReturn(10);
        $parkingRepo->method('findById')->willReturn($parking);
        $session = $this->createMock(ParkingSession::class);
        $session->method('getEntryDateTime')->willReturn(new \DateTimeImmutable('-1 hour'));
        $session->method('getExitDateTime')->willReturn(null);
        $sessionRepo->method('findByParkingId')->willReturn([$session]);
        $useCase = new GetAvailableSpotsUseCase($parkingRepo, $sessionRepo);
        $request = new GetAvailableSpotsRequest(1, new \DateTimeImmutable());
        $result = $useCase->execute($request);
        $this->assertEquals(9, $result);
    }
    public function testExecuteThrowsIfParkingNotFound()
    {
        $parkingRepo = $this->createMock(ParkingRepositoryInterface::class);
        $sessionRepo = $this->createMock(ParkingSessionRepositoryInterface::class);
        $parkingRepo->method('findById')->willReturn(null);
        $useCase = new GetAvailableSpotsUseCase($parkingRepo, $sessionRepo);
        $request = new GetAvailableSpotsRequest(1, new \DateTimeImmutable());
        $this->expectException(\InvalidArgumentException::class);
        $useCase->execute($request);
    }
}


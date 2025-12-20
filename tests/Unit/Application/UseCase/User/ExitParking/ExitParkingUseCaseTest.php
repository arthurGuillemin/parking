<?php

namespace Tests\Unit\Application\UseCase\User\ExitParking;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Application\UseCase\User\ExitParking\ExitParkingUseCase;
use App\Application\UseCase\User\ExitParking\ExitParkingRequest;
use App\Domain\Entity\Reservation;
use App\Domain\Entity\ParkingSession;
use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;

class ExitParkingUseCaseTest extends TestCase
{
    private ExitParkingUseCase $useCase;
    private MockObject|ParkingSessionRepositoryInterface $parkingSessionRepository;
    private MockObject|ReservationRepositoryInterface $reservationRepository;

    protected function setUp(): void
    {
        $this->parkingSessionRepository = $this->createMock(ParkingSessionRepositoryInterface::class);
        $this->reservationRepository = $this->createMock(ReservationRepositoryInterface::class);

        $this->useCase = new ExitParkingUseCase(
            $this->parkingSessionRepository,
            $this->reservationRepository
        );
    }

    public function testExecuteSuccess(): void
    {
        $request = new ExitParkingRequest('user-1', 1);

        // 1. Active Session
        $session = new ParkingSession(
            500,
            'user-1',
            1,
            200, // reservationId
            new \DateTimeImmutable('2025-01-01 10:00'),
            null,
            null,
            false
        );
        $this->parkingSessionRepository->method('findActiveSessionByUserId')->willReturn($session);

        // 2. Associated Reservation
        $reservation = new Reservation(
            200,
            'user-1',
            1,
            new \DateTimeImmutable('2025-01-01 10:00'),
            new \DateTimeImmutable('2025-01-01 12:00'),
            'active',
            15.0,
            null
        );
        $this->reservationRepository->method('findById')->with(200)->willReturn($reservation);

        // 3. Expect Updates
        $this->reservationRepository->expects($this->once())->method('save')->willReturnArgument(0); // with updated status/end/amount
        $this->parkingSessionRepository->expects($this->once())->method('save')->willReturnArgument(0); // with exitTime/amount

        $response = $this->useCase->execute($request);

        $this->assertNotNull($response->exitDateTime);
        $this->assertEquals(15.0, $response->amount);
    }

    public function testExecuteFailsIfNoSession(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("not currently in a parking session");

        $request = new ExitParkingRequest('user-1', 1);
        $this->parkingSessionRepository->method('findActiveSessionByUserId')->willReturn(null);

        $this->useCase->execute($request);
    }
}

<?php

namespace Tests\Unit\Application\UseCase\User\MakeReservation;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Application\UseCase\User\MakeReservation\MakeReservationUseCase;
use App\Application\UseCase\User\MakeReservation\MakeReservationRequest;
use App\Domain\Entity\Parking;
use App\Domain\Entity\Reservation;
use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Service\CheckAvailabilityService;
use App\Domain\Repository\PricingRuleRepositoryInterface; // Optional

class MakeReservationUseCaseTest extends TestCase
{
    private MakeReservationUseCase $useCase;
    private MockObject|ReservationRepositoryInterface $reservationRepository;
    private MockObject|ParkingRepositoryInterface $parkingRepository;
    private MockObject|CheckAvailabilityService $checkAvailabilityService;
    private MockObject|PricingRuleRepositoryInterface $pricingRuleRepository;

    protected function setUp(): void
    {
        $this->reservationRepository = $this->createMock(ReservationRepositoryInterface::class);
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);
        $this->checkAvailabilityService = $this->createMock(CheckAvailabilityService::class);
        $this->pricingRuleRepository = $this->createMock(PricingRuleRepositoryInterface::class);

        $this->useCase = new MakeReservationUseCase(
            $this->reservationRepository,
            $this->parkingRepository,
            $this->checkAvailabilityService,
            $this->pricingRuleRepository
        );
    }

    public function testExecuteSuccess(): void
    {
        $request = new MakeReservationRequest(
            'user-1',
            1,
            new \DateTimeImmutable('2025-01-01 10:00'),
            new \DateTimeImmutable('2025-01-01 12:00')
        );

        $parking = new Parking(1, 'owner-1', 'Parking 1', 'Address', 0.0, 0.0, 10, true);

        $this->parkingRepository->method('findById')->willReturn($parking);
        $this->checkAvailabilityService->method('checkAvailability')->willReturn(true);
        // Pricing mock optional, returns null or rule

        $this->reservationRepository->expects($this->once())
            ->method('save')
            ->willReturnArgument(0); // Return the reservation passed to save

        $response = $this->useCase->execute($request);

        $this->assertEquals(1, $response->parkingId);
        $this->assertEquals('user-1', $response->userId);
        $this->assertEquals('pending', $response->status);
    }

    public function testExecuteFailsIfFull(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Parking is full");

        $request = new MakeReservationRequest(
            'user-1',
            1,
            new \DateTimeImmutable('2025-01-01 10:00'),
            new \DateTimeImmutable('2025-01-01 12:00')
        );

        $parking = new Parking(1, 'owner-1', 'Parking 1', 'Address', 0.0, 0.0, 10);

        $this->parkingRepository->method('findById')->willReturn($parking);
        $this->checkAvailabilityService->method('checkAvailability')->willReturn(false);

        $this->useCase->execute($request);
    }
}

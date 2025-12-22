<?php

namespace Unit\Application\UseCase\User\MakeReservation;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\User\MakeReservation\MakeReservationUseCase;
use App\Application\UseCase\User\MakeReservation\MakeReservationRequest;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Service\CheckAvailabilityService;
use App\Domain\Service\PricingService;
use App\Domain\Entity\Parking;
use App\Domain\Entity\Reservation;
use App\Application\DTO\Response\ReservationResponse;
use DateTimeImmutable;

class MakeReservationUseCaseTest extends TestCase
{
    public function testSuccessfulReservation()
    {
        // Mocks
        $reservationRepo = $this->createMock(ReservationRepositoryInterface::class);
        $parkingRepo = $this->createMock(ParkingRepositoryInterface::class);
        $availabilityService = $this->createMock(CheckAvailabilityService::class);
        $pricingService = $this->createMock(PricingService::class);

        // Data
        $start = new DateTimeImmutable('now');
        $end = $start->modify('+2 hours');
        $parking = $this->createMock(Parking::class);
        $parking->method('getParkingId')->willReturn(1);

        // Expectations
        $parkingRepo->method('findById')->willReturn($parking);
        $availabilityService->method('checkAvailability')->willReturn(true);
        $pricingService->method('calculatePrice')->willReturn(15.0);

        // Mock Save to return a reservation with ID
        $reservationRepo->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Reservation $res) {
                // Return same reservation but "simulated" saved state logic if needed
                return $res;
            });

        $useCase = new MakeReservationUseCase(
            $reservationRepo,
            $parkingRepo,
            $availabilityService,
            $pricingService
        );

        $request = new MakeReservationRequest('user1', 1, $start, $end);
        $response = $useCase->execute($request);

        $this->assertInstanceOf(ReservationResponse::class, $response);
        $this->assertEquals(15.0, $response->amount); // Accessed directly from DTO
        $this->assertEquals('pending', $response->status);
    }

    public function testThrowsExceptionIfParkingFull()
    {
        $reservationRepo = $this->createMock(ReservationRepositoryInterface::class);
        $parkingRepo = $this->createMock(ParkingRepositoryInterface::class);
        $availabilityService = $this->createMock(CheckAvailabilityService::class);
        $pricingService = $this->createMock(PricingService::class);

        $parking = $this->createMock(Parking::class);
        $parkingRepo->method('findById')->willReturn($parking);
        $availabilityService->method('checkAvailability')->willReturn(false); // FULL!

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Parking is full during this period.");

        $useCase = new MakeReservationUseCase(
            $reservationRepo,
            $parkingRepo,
            $availabilityService,
            $pricingService
        );

        $request = new MakeReservationRequest('user1', 1, new DateTimeImmutable(), new DateTimeImmutable());
        $useCase->execute($request);
    }
}

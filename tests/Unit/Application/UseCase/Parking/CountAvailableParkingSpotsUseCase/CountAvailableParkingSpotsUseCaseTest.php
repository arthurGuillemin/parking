<?php

namespace Unit\Application\UseCase\Parking\CountAvailableParkingSpotsUseCase;

use App\Application\DTO\Parking\CountAvailableParkingSpotsRequest;
use App\Application\UseCase\Parking\CountAvailableParkingSpots\CountAvailableParkingSpotsUseCase;
use App\Domain\Entity\Parking;
use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use PHPUnit\Framework\TestCase;

class CountAvailableParkingSpotsUseCaseTest extends TestCase
{
    public function testReturnsAvailableSpots()
    {
        $parkingRepository = $this->createMock(ParkingRepositoryInterface::class);
        $reservationRepository = $this->createMock(ReservationRepositoryInterface::class);
        $subscriptionRepository = $this->createMock(SubscriptionRepositoryInterface::class);

        $parking = $this->createMock(Parking::class);
        $parking->method('getTotalCapacity')->willReturn(10);
        $parkingRepository->method('findById')->willReturn($parking);
        $reservationRepository->method('findForParkingBetween')->willReturn([1, 2]); // 2 reservations
        $subscriptionRepository->method('findByParkingIdAndMonth')->willReturn([]); // 0 subscriptions

        $useCase = new CountAvailableParkingSpotsUseCase(
            $parkingRepository,
            $reservationRepository,
            $subscriptionRepository
        );
        $request = new CountAvailableParkingSpotsRequest(1, new \DateTimeImmutable());
        $result = $useCase->execute($request);
        $this->assertEquals(8, $result->availableSpots);
    }

    public function testThrowsExceptionIfParkingNotFound()
    {
        $parkingRepository = $this->createMock(ParkingRepositoryInterface::class);
        $reservationRepository = $this->createMock(ReservationRepositoryInterface::class);
        $subscriptionRepository = $this->createMock(SubscriptionRepositoryInterface::class);
        $parkingRepository->method('findById')->willReturn(null);
        $useCase = new CountAvailableParkingSpotsUseCase(
            $parkingRepository,
            $reservationRepository,
            $subscriptionRepository
        );
        $request = new CountAvailableParkingSpotsRequest(1, new \DateTimeImmutable());
        $this->expectException(\InvalidArgumentException::class);
        $useCase->execute($request);
    }

    public function testActiveSubscriptionCountsAsOccupied()
    {
        $parkingRepository = $this->createMock(ParkingRepositoryInterface::class);
        $reservationRepository = $this->createMock(ReservationRepositoryInterface::class);
        $subscriptionRepository = $this->createMock(SubscriptionRepositoryInterface::class);

        $parking = $this->createMock(Parking::class);
        $parking->method('getTotalCapacity')->willReturn(10);
        $parkingRepository->method('findById')->willReturn($parking);
        $reservationRepository->method('findForParkingBetween')->willReturn([]); // 0 reservations

        $now = new \DateTimeImmutable();
        $subscription = new \App\Domain\Entity\Subscription(
            1, // id
            'user-uuid', // userId
            1, // parkingId
            1, // typeId
            $now->modify('-1 month'), // startDate
            null, // endDate
            'active', // status
            50.0 // monthlyPrice
        );
        $subscriptionRepository->method('findByParkingIdAndMonth')->willReturn([$subscription]);

        $useCase = new CountAvailableParkingSpotsUseCase(
            $parkingRepository,
            $reservationRepository,
            $subscriptionRepository
        );
        $request = new CountAvailableParkingSpotsRequest(1, $now);
        $result = $useCase->execute($request);
        $this->assertEquals(9, $result->availableSpots);
    }

    public function testInactiveSubscriptionDoesNotCountAsOccupied()
    {
        $parkingRepository = $this->createMock(ParkingRepositoryInterface::class);
        $reservationRepository = $this->createMock(ReservationRepositoryInterface::class);
        $subscriptionRepository = $this->createMock(SubscriptionRepositoryInterface::class);

        $parking = $this->createMock(Parking::class);
        $parking->method('getTotalCapacity')->willReturn(10);
        $parkingRepository->method('findById')->willReturn($parking);
        $reservationRepository->method('findForParkingBetween')->willReturn([]); // 0 reservations

        $now = new \DateTimeImmutable();
        $subscription = new \App\Domain\Entity\Subscription(
            2, // id
            'user-uuid', // userId
            1, // parkingId
            1, // typeId
            $now->modify('-2 months'), // startDate
            $now->modify('-1 month'), // endDate (expired)
            'active', // status
            50.0 // monthlyPrice
        );
        $subscriptionRepository->method('findByParkingIdAndMonth')->willReturn([$subscription]);

        $useCase = new CountAvailableParkingSpotsUseCase(
            $parkingRepository,
            $reservationRepository,
            $subscriptionRepository
        );
        $request = new CountAvailableParkingSpotsRequest(1, $now);
        $result = $useCase->execute($request);
        $this->assertEquals(10, $result->availableSpots);
    }

    public function testOccupiedSpotsExceedCapacityReturnsZero()
    {
        $parkingRepository = $this->createMock(ParkingRepositoryInterface::class);
        $reservationRepository = $this->createMock(ReservationRepositoryInterface::class);
        $subscriptionRepository = $this->createMock(SubscriptionRepositoryInterface::class);

        $parking = $this->createMock(Parking::class);
        $parking->method('getTotalCapacity')->willReturn(2);
        $parkingRepository->method('findById')->willReturn($parking);
        $reservationRepository->method('findForParkingBetween')->willReturn([1, 2, 3]); // 3 reservations
        $subscriptionRepository->method('findByParkingIdAndMonth')->willReturn([]);

        $useCase = new CountAvailableParkingSpotsUseCase(
            $parkingRepository,
            $reservationRepository,
            $subscriptionRepository
        );
        $request = new CountAvailableParkingSpotsRequest(1, new \DateTimeImmutable());
        $result = $useCase->execute($request);
        $this->assertEquals(0, $result->availableSpots);
    }

    public function testSubscriptionWithoutIsActiveAtMethodCountsAsActive()
    {
        $parkingRepository = $this->createMock(ParkingRepositoryInterface::class);
        $reservationRepository = $this->createMock(ReservationRepositoryInterface::class);
        $subscriptionRepository = $this->createMock(SubscriptionRepositoryInterface::class);
        $parking = $this->createMock(Parking::class);
        $parking->method('getTotalCapacity')->willReturn(10);
        $parkingRepository->method('findById')->willReturn($parking);
        $reservationRepository->method('findForParkingBetween')->willReturn([]);
        $now = new \DateTimeImmutable();
        // Class anonyme sans la méthode isActiveAt
        $subscription = new class($now) {
            public function __construct(private $startDate) {}
            public function getStartDate() { return $this->startDate; }
            public function getEndDate() { return null; }
        };
        $subscriptionRepository->method('findByParkingIdAndMonth')->willReturn([$subscription]);
        $useCase = new CountAvailableParkingSpotsUseCase(
            $parkingRepository,
            $reservationRepository,
            $subscriptionRepository
        );
        $request = new CountAvailableParkingSpotsRequest(1, $now);
        $result = $useCase->execute($request);
        $this->assertEquals(9, $result->availableSpots);
    }
}

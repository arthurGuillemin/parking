<?php
namespace Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\Parking;
use App\Domain\Service\ParkingAvailabilityService;
use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Repository\OpeningHourRepositoryInterface;
use App\Domain\Entity\OpeningHour;
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
        $openingHourRepo = $this->createMock(\App\Domain\Repository\OpeningHourRepositoryInterface::class);
        $reservationRepo = $this->createMock(\App\Domain\Repository\ReservationRepositoryInterface::class);
        $subscriptionRepo = $this->createMock(\App\Domain\Repository\SubscriptionRepositoryInterface::class);
        $service = new ParkingAvailabilityService($parkingRepo, $sessionRepo, $openingHourRepo, $reservationRepo, $subscriptionRepo);
        $request = new GetAvailableSpotsRequest(1, new \DateTimeImmutable());
        $result = $service->getAvailableSpots($request);
        $this->assertIsInt($result);
    }

    public function testIsAvailableReturnsTrueForOpen247AndSpotsAvailable()
    {
        $parking = $this->createMock(Parking::class);
        $parking->method('isOpen24_7')->willReturn(true);
        $parking->method('getParkingId')->willReturn(1);
        $openingHourRepo = $this->createMock(OpeningHourRepositoryInterface::class);
        $parkingRepo = $this->createMock(ParkingRepositoryInterface::class);
        $sessionRepo = $this->createMock(ParkingSessionRepositoryInterface::class);
        $reservationRepo = $this->createMock(\App\Domain\Repository\ReservationRepositoryInterface::class);
        $subscriptionRepo = $this->createMock(\App\Domain\Repository\SubscriptionRepositoryInterface::class);
        $service = new ParkingAvailabilityService($parkingRepo, $sessionRepo, $openingHourRepo, $reservationRepo, $subscriptionRepo);
        $service = $this->getMockBuilder(ParkingAvailabilityService::class)
            ->setConstructorArgs([$parkingRepo, $sessionRepo, $openingHourRepo, $reservationRepo, $subscriptionRepo])
            ->onlyMethods(['getAvailableSpots'])
            ->getMock();
        $service->expects($this->once())
            ->method('getAvailableSpots')
            ->willReturn(2);
        $this->assertTrue($service->isAvailable($parking, new \DateTimeImmutable()));
    }

    public function testIsAvailableReturnsFalseIfClosed()
    {
        $parking = $this->createMock(Parking::class);
        $parking->method('isOpen24_7')->willReturn(false);
        $parking->method('getParkingId')->willReturn(1);
        $openingHour = $this->createMock(OpeningHour::class);
        $openingHour->method('getWeekdayStart')->willReturn(1);
        $openingHour->method('getWeekdayEnd')->willReturn(1);
        $openingHour->method('getOpeningTime')->willReturn(new \DateTimeImmutable('08:00:00'));
        $openingHour->method('getClosingTime')->willReturn(new \DateTimeImmutable('18:00:00'));
        $openingHourRepo = $this->createMock(OpeningHourRepositoryInterface::class);
        $openingHourRepo->method('findByParkingId')->willReturn([$openingHour]);
        $parkingRepo = $this->createMock(ParkingRepositoryInterface::class);
        $sessionRepo = $this->createMock(ParkingSessionRepositoryInterface::class);
        $reservationRepo = $this->createMock(\App\Domain\Repository\ReservationRepositoryInterface::class);
        $subscriptionRepo = $this->createMock(\App\Domain\Repository\SubscriptionRepositoryInterface::class);
        $service = $this->getMockBuilder(ParkingAvailabilityService::class)
            ->setConstructorArgs([$parkingRepo, $sessionRepo, $openingHourRepo, $reservationRepo, $subscriptionRepo])
            ->onlyMethods(['getAvailableSpots'])
            ->getMock();
        $service->expects($this->never())
            ->method('getAvailableSpots');
        // Dimanche (7), donc fermé
        $date = new \DateTimeImmutable('next sunday 10:00:00');
        $this->assertFalse($service->isAvailable($parking, $date));
    }

    public function testIsAvailableReturnsTrueIfOpenAndSpotsAvailable()
    {
        $parking = $this->createMock(Parking::class);
        $parking->method('isOpen24_7')->willReturn(false);
        $parking->method('getParkingId')->willReturn(1);
        $openingHour = $this->createMock(OpeningHour::class);
        $openingHour->method('getWeekdayStart')->willReturn(1);
        $openingHour->method('getWeekdayEnd')->willReturn(7);
        $openingHour->method('getOpeningTime')->willReturn(new \DateTimeImmutable('08:00:00'));
        $openingHour->method('getClosingTime')->willReturn(new \DateTimeImmutable('18:00:00'));
        $openingHourRepo = $this->createMock(OpeningHourRepositoryInterface::class);
        $openingHourRepo->method('findByParkingId')->willReturn([$openingHour]);
        $parkingRepo = $this->createMock(ParkingRepositoryInterface::class);
        $sessionRepo = $this->createMock(ParkingSessionRepositoryInterface::class);
        $reservationRepo = $this->createMock(\App\Domain\Repository\ReservationRepositoryInterface::class);
        $subscriptionRepo = $this->createMock(\App\Domain\Repository\SubscriptionRepositoryInterface::class);
        $service = $this->getMockBuilder(ParkingAvailabilityService::class)
            ->setConstructorArgs([$parkingRepo, $sessionRepo, $openingHourRepo, $reservationRepo, $subscriptionRepo])
            ->onlyMethods(['getAvailableSpots'])
            ->getMock();
        $service->expects($this->once())
            ->method('getAvailableSpots')
            ->willReturn(1);
        $date = new \DateTimeImmutable('monday 10:00:00');
        $this->assertTrue($service->isAvailable($parking, $date));
    }

    public function testIsAvailableReturnsFalseIfOpenButNoSpots()
    {
        $parking = $this->createMock(Parking::class);
        $parking->method('isOpen24_7')->willReturn(false);
        $parking->method('getParkingId')->willReturn(1);
        $openingHour = $this->createMock(OpeningHour::class);
        $openingHour->method('getWeekdayStart')->willReturn(1);
        $openingHour->method('getWeekdayEnd')->willReturn(7);
        $openingHour->method('getOpeningTime')->willReturn(new \DateTimeImmutable('08:00:00'));
        $openingHour->method('getClosingTime')->willReturn(new \DateTimeImmutable('18:00:00'));
        $openingHourRepo = $this->createMock(OpeningHourRepositoryInterface::class);
        $openingHourRepo->method('findByParkingId')->willReturn([$openingHour]);
        $parkingRepo = $this->createMock(ParkingRepositoryInterface::class);
        $sessionRepo = $this->createMock(ParkingSessionRepositoryInterface::class);
        $reservationRepo = $this->createMock(\App\Domain\Repository\ReservationRepositoryInterface::class);
        $subscriptionRepo = $this->createMock(\App\Domain\Repository\SubscriptionRepositoryInterface::class);
        $service = $this->getMockBuilder(ParkingAvailabilityService::class)
            ->setConstructorArgs([$parkingRepo, $sessionRepo, $openingHourRepo, $reservationRepo, $subscriptionRepo])
            ->onlyMethods(['getAvailableSpots'])
            ->getMock();
        $service->expects($this->once())
            ->method('getAvailableSpots')
            ->willReturn(0);
        $date = new \DateTimeImmutable('monday 10:00:00');
        $this->assertFalse($service->isAvailable($parking, $date));
    }
}

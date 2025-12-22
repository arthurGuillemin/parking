<?php
namespace Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\Parking;
use App\Domain\Service\ParkingAvailabilityService;
<<<<<<< HEAD
use App\Application\UseCase\Owner\GetAvailableSpots\GetAvailableSpotsUseCase;
use App\Application\UseCase\Owner\GetAvailableSpots\GetAvailableSpotsRequest;
use App\Domain\Repository\OpeningHourRepositoryInterface;
use App\Domain\Entity\OpeningHour;
=======
use App\Domain\Repository\OpeningHourRepositoryInterface;
use App\Domain\Entity\OpeningHour;
use App\Application\UseCase\Owner\GetAvailableSpots\GetAvailableSpotsRequest;
use App\Application\UseCase\Owner\GetAvailableSpots\GetAvailableSpotsUseCase;
>>>>>>> main

class ParkingAvailabilityServiceTest extends TestCase
{
    private $getAvailableSpotsUseCase;
    private $openingHourRepo;

    protected function setUp(): void
    {
        $this->getAvailableSpotsUseCase = $this->createStub(GetAvailableSpotsUseCase::class);
        $this->openingHourRepo = $this->createStub(OpeningHourRepositoryInterface::class);
    }

    public function testGetAvailableSpotsDelegatesToUseCase()
    {
<<<<<<< HEAD
        $this->getAvailableSpotsUseCase->method('execute')->willReturn(5);

        $service = new ParkingAvailabilityService($this->getAvailableSpotsUseCase, $this->openingHourRepo);
        $request = new GetAvailableSpotsRequest(1, new \DateTimeImmutable());
        $result = $service->getAvailableSpots($request);

        $this->assertIsInt($result);
        $this->assertEquals(5, $result);
=======
        $getAvailableSpotsUseCase = $this->createMock(GetAvailableSpotsUseCase::class);
        $openingHourRepo = $this->createMock(OpeningHourRepositoryInterface::class);

        $service = new ParkingAvailabilityService($getAvailableSpotsUseCase, $openingHourRepo);

        $getAvailableSpotsUseCase->expects($this->once())
            ->method('execute')
            ->willReturn(10);

        $request = new GetAvailableSpotsRequest(1, new \DateTimeImmutable());
        $result = $service->getAvailableSpots($request);
        $this->assertEquals(10, $result);
>>>>>>> main
    }

    public function testIsAvailableReturnsTrueForOpen247AndSpotsAvailable()
    {
        $parking = $this->createStub(Parking::class);
        $parking->method('isOpen24_7')->willReturn(true);
        $parking->method('getParkingId')->willReturn(1);

<<<<<<< HEAD
        $this->getAvailableSpotsUseCase->method('execute')->willReturn(2);

        $service = new ParkingAvailabilityService($this->getAvailableSpotsUseCase, $this->openingHourRepo);
=======
        $getAvailableSpotsUseCase = $this->createMock(GetAvailableSpotsUseCase::class);
        $openingHourRepo = $this->createMock(OpeningHourRepositoryInterface::class);

        $getAvailableSpotsUseCase->method('execute')->willReturn(2);

        $service = new ParkingAvailabilityService($getAvailableSpotsUseCase, $openingHourRepo);
>>>>>>> main

        $this->assertTrue($service->isAvailable($parking, new \DateTimeImmutable()));
    }

    public function testIsAvailableReturnsFalseIfClosed()
    {
        $parking = $this->createStub(Parking::class);
        $parking->method('isOpen24_7')->willReturn(false);
        $parking->method('getParkingId')->willReturn(1);

<<<<<<< HEAD
        // Opening hours only on Monday (weekday 1)
        $openingHour = $this->createStub(OpeningHour::class);
        $openingHour->method('getWeekdayStart')->willReturn(1);
        $openingHour->method('getWeekdayEnd')->willReturn(1);
        $openingHour->method('getOpeningTime')->willReturn(new \DateTimeImmutable('08:00:00'));
        $openingHour->method('getClosingTime')->willReturn(new \DateTimeImmutable('18:00:00'));
        $this->openingHourRepo->method('findByParkingId')->willReturn([$openingHour]);

        $service = new ParkingAvailabilityService($this->getAvailableSpotsUseCase, $this->openingHourRepo);

        // Sunday (7) - should be closed
=======
        $openingHourRepo = $this->createMock(OpeningHourRepositoryInterface::class);
        $openingHourRepo->method('findByParkingId')->willReturn([]); // No opening hours = closed

        $getAvailableSpotsUseCase = $this->createMock(GetAvailableSpotsUseCase::class);

        $service = new ParkingAvailabilityService($getAvailableSpotsUseCase, $openingHourRepo);

        // Dimanche (7), donc fermé si pas d'horaires
>>>>>>> main
        $date = new \DateTimeImmutable('next sunday 10:00:00');
        $this->assertFalse($service->isAvailable($parking, $date));
    }

    public function testIsAvailableReturnsTrueIfOpenAndSpotsAvailable()
    {
        $parking = $this->createStub(Parking::class);
        $parking->method('isOpen24_7')->willReturn(false);
        $parking->method('getParkingId')->willReturn(1);

<<<<<<< HEAD
        // Opening hours Monday-Sunday
        $openingHour = $this->createStub(OpeningHour::class);
=======
        $openingHour = $this->createMock(OpeningHour::class);
>>>>>>> main
        $openingHour->method('getWeekdayStart')->willReturn(1);
        $openingHour->method('getWeekdayEnd')->willReturn(7);
        $openingHour->method('getOpeningTime')->willReturn(new \DateTimeImmutable('08:00:00'));
        $openingHour->method('getClosingTime')->willReturn(new \DateTimeImmutable('18:00:00'));
<<<<<<< HEAD
        $this->openingHourRepo->method('findByParkingId')->willReturn([$openingHour]);

        $this->getAvailableSpotsUseCase->method('execute')->willReturn(1);

        $service = new ParkingAvailabilityService($this->getAvailableSpotsUseCase, $this->openingHourRepo);
=======

        $openingHourRepo = $this->createMock(OpeningHourRepositoryInterface::class);
        $openingHourRepo->method('findByParkingId')->willReturn([$openingHour]);

        $getAvailableSpotsUseCase = $this->createMock(GetAvailableSpotsUseCase::class);
        $getAvailableSpotsUseCase->method('execute')->willReturn(1);

        $service = new ParkingAvailabilityService($getAvailableSpotsUseCase, $openingHourRepo);
>>>>>>> main

        $date = new \DateTimeImmutable('monday 10:00:00');
        $this->assertTrue($service->isAvailable($parking, $date));
    }

    public function testIsAvailableReturnsFalseIfOpenButNoSpots()
    {
        $parking = $this->createStub(Parking::class);
        $parking->method('isOpen24_7')->willReturn(false);
        $parking->method('getParkingId')->willReturn(1);

<<<<<<< HEAD
        // Opening hours Monday-Sunday
        $openingHour = $this->createStub(OpeningHour::class);
=======
        $openingHour = $this->createMock(OpeningHour::class);
>>>>>>> main
        $openingHour->method('getWeekdayStart')->willReturn(1);
        $openingHour->method('getWeekdayEnd')->willReturn(7);
        $openingHour->method('getOpeningTime')->willReturn(new \DateTimeImmutable('08:00:00'));
        $openingHour->method('getClosingTime')->willReturn(new \DateTimeImmutable('18:00:00'));
<<<<<<< HEAD
        $this->openingHourRepo->method('findByParkingId')->willReturn([$openingHour]);

        $this->getAvailableSpotsUseCase->method('execute')->willReturn(0);

        $service = new ParkingAvailabilityService($this->getAvailableSpotsUseCase, $this->openingHourRepo);
=======

        $openingHourRepo = $this->createMock(OpeningHourRepositoryInterface::class);
        $openingHourRepo->method('findByParkingId')->willReturn([$openingHour]);

        $getAvailableSpotsUseCase = $this->createMock(GetAvailableSpotsUseCase::class);
        $getAvailableSpotsUseCase->method('execute')->willReturn(0);

        $service = new ParkingAvailabilityService($getAvailableSpotsUseCase, $openingHourRepo);
>>>>>>> main

        $date = new \DateTimeImmutable('monday 10:00:00');
        $this->assertFalse($service->isAvailable($parking, $date));
    }
}

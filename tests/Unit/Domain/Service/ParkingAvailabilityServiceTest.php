<?php
namespace Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\Parking;
use App\Domain\Service\ParkingAvailabilityService;
use App\Application\UseCase\Owner\GetAvailableSpots\GetAvailableSpotsUseCase;
use App\Application\UseCase\Owner\GetAvailableSpots\GetAvailableSpotsRequest;
use App\Domain\Repository\OpeningHourRepositoryInterface;
use App\Domain\Entity\OpeningHour;

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
        $this->getAvailableSpotsUseCase->method('execute')->willReturn(5);

        $service = new ParkingAvailabilityService($this->getAvailableSpotsUseCase, $this->openingHourRepo);
        $request = new GetAvailableSpotsRequest(1, new \DateTimeImmutable());
        $result = $service->getAvailableSpots($request);

        $this->assertIsInt($result);
        $this->assertEquals(5, $result);
    }

    public function testIsAvailableReturnsTrueForOpen247AndSpotsAvailable()
    {
        $parking = $this->createStub(Parking::class);
        $parking->method('isOpen24_7')->willReturn(true);
        $parking->method('getParkingId')->willReturn(1);

        $this->getAvailableSpotsUseCase->method('execute')->willReturn(2);

        $service = new ParkingAvailabilityService($this->getAvailableSpotsUseCase, $this->openingHourRepo);

        $this->assertTrue($service->isAvailable($parking, new \DateTimeImmutable()));
    }

    public function testIsAvailableReturnsFalseIfClosed()
    {
        $parking = $this->createStub(Parking::class);
        $parking->method('isOpen24_7')->willReturn(false);
        $parking->method('getParkingId')->willReturn(1);

        // Opening hours only on Monday (weekday 1)
        $openingHour = $this->createStub(OpeningHour::class);
        $openingHour->method('getWeekdayStart')->willReturn(1);
        $openingHour->method('getWeekdayEnd')->willReturn(1);
        $openingHour->method('getOpeningTime')->willReturn(new \DateTimeImmutable('08:00:00'));
        $openingHour->method('getClosingTime')->willReturn(new \DateTimeImmutable('18:00:00'));
        $this->openingHourRepo->method('findByParkingId')->willReturn([$openingHour]);

        $service = new ParkingAvailabilityService($this->getAvailableSpotsUseCase, $this->openingHourRepo);

        // Sunday (7) - should be closed
        $date = new \DateTimeImmutable('next sunday 10:00:00');
        $this->assertFalse($service->isAvailable($parking, $date));
    }

    public function testIsAvailableReturnsTrueIfOpenAndSpotsAvailable()
    {
        $parking = $this->createStub(Parking::class);
        $parking->method('isOpen24_7')->willReturn(false);
        $parking->method('getParkingId')->willReturn(1);

        // Opening hours Monday-Sunday
        $openingHour = $this->createStub(OpeningHour::class);
        $openingHour->method('getWeekdayStart')->willReturn(1);
        $openingHour->method('getWeekdayEnd')->willReturn(7);
        $openingHour->method('getOpeningTime')->willReturn(new \DateTimeImmutable('08:00:00'));
        $openingHour->method('getClosingTime')->willReturn(new \DateTimeImmutable('18:00:00'));
        $this->openingHourRepo->method('findByParkingId')->willReturn([$openingHour]);

        $this->getAvailableSpotsUseCase->method('execute')->willReturn(1);

        $service = new ParkingAvailabilityService($this->getAvailableSpotsUseCase, $this->openingHourRepo);

        $date = new \DateTimeImmutable('monday 10:00:00');
        $this->assertTrue($service->isAvailable($parking, $date));
    }

    public function testIsAvailableReturnsFalseIfOpenButNoSpots()
    {
        $parking = $this->createStub(Parking::class);
        $parking->method('isOpen24_7')->willReturn(false);
        $parking->method('getParkingId')->willReturn(1);

        // Opening hours Monday-Sunday
        $openingHour = $this->createStub(OpeningHour::class);
        $openingHour->method('getWeekdayStart')->willReturn(1);
        $openingHour->method('getWeekdayEnd')->willReturn(7);
        $openingHour->method('getOpeningTime')->willReturn(new \DateTimeImmutable('08:00:00'));
        $openingHour->method('getClosingTime')->willReturn(new \DateTimeImmutable('18:00:00'));
        $this->openingHourRepo->method('findByParkingId')->willReturn([$openingHour]);

        $this->getAvailableSpotsUseCase->method('execute')->willReturn(0);

        $service = new ParkingAvailabilityService($this->getAvailableSpotsUseCase, $this->openingHourRepo);

        $date = new \DateTimeImmutable('monday 10:00:00');
        $this->assertFalse($service->isAvailable($parking, $date));
    }
}

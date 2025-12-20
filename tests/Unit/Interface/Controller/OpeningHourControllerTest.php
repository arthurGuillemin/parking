<?php
namespace Unit\Interface\Controller;

use PHPUnit\Framework\TestCase;
use App\Interface\Controller\OpeningHourController;
use App\Domain\Service\OpeningHourService;
use App\Application\UseCase\Owner\UpdateOpeningHour\UpdateOpeningHourRequest;
use App\Domain\Entity\OpeningHour;

class OpeningHourControllerTest extends TestCase
{
    public function testUpdateReturnsArray()
    {
        $mockService = $this->createMock(OpeningHourService::class);
        $mockOpeningHour = $this->createMock(OpeningHour::class);
        $mockOpeningHour->method('getOpeningHourId')->willReturn(1);
        $mockOpeningHour->method('getParkingId')->willReturn(2);
        $mockOpeningHour->method('getWeekdayStart')->willReturn(3);
        $mockOpeningHour->method('getWeekdayEnd')->willReturn(3);
        $mockOpeningHour->method('getOpeningTime')->willReturn(new \DateTimeImmutable('08:00:00'));
        $mockOpeningHour->method('getClosingTime')->willReturn(new \DateTimeImmutable('20:00:00'));
        $mockService->method('updateOpeningHour')->willReturn($mockOpeningHour);
        $controller = new OpeningHourController($mockService);
        $data = [
            'parkingId' => 2,
            'weekdayStart' => 3,
            'weekdayEnd' => 3,
            'openingTime' => '08:00:00',
            'closingTime' => '20:00:00'
        ];
        $result = $controller->update($data);
        $this->assertEquals([
            'id' => 1,
            'parkingId' => 2,
            'weekdayStart' => 3,
            'weekdayEnd' => 3,
            'openingTime' => '08:00:00',
            'closingTime' => '20:00:00',
        ], $result);
    }
    public function testUpdateThrowsOnMissingFields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $controller = new OpeningHourController($this->createMock(OpeningHourService::class));
        $controller->update(['parkingId' => 2]);
    }
}

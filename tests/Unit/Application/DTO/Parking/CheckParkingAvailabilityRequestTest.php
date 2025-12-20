<?php
namespace Unit\Application\DTO\Parking;

use App\Application\DTO\Parking\CheckParkingAvailability\CheckParkingAvailabilityRequest;
use PHPUnit\Framework\TestCase;

class CheckParkingAvailabilityRequestTest extends TestCase
{
    public function testConstructorAndProperties()
    {
        $parkingId = 42;
        $dateTime = new \DateTimeImmutable('2025-12-05 10:00:00');
        $dto = new CheckParkingAvailabilityRequest($parkingId, $dateTime);
        $this->assertSame($parkingId, $dto->parkingId);
        $this->assertSame($dateTime, $dto->dateTime);
    }
}


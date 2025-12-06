<?php
namespace Unit\Application\DTO\Parking;

use App\Application\DTO\Parking\CheckParkingAvailability\CheckParkingAvailabilityResponse;
use PHPUnit\Framework\TestCase;

class CheckParkingAvailabilityResponseTest extends TestCase
{
    public function testConstructorAndProperties()
    {
        $available = true;
        $message = 'Parking disponible';
        $dto = new CheckParkingAvailabilityResponse($available, $message);
        $this->assertSame($available, $dto->available);
        $this->assertSame($message, $dto->message);

        $dto2 = new CheckParkingAvailabilityResponse(false);
        $this->assertFalse($dto2->available);
        $this->assertNull($dto2->message);
    }
}


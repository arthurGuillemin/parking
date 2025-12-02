<?php

namespace Unit\Domain\Entity;

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\Parking;

class ParkingTest extends TestCase
{
    public function testGetters()
    {
        $parking = new Parking(1, 'owner-uuid', 'Test Parking', '123 Main St', 1.23, 4.56, 100, true);
        $this->assertEquals(1, $parking->getParkingId());
        $this->assertEquals('owner-uuid', $parking->getOwnerId());
        $this->assertEquals('Test Parking', $parking->getName());
        $this->assertEquals('123 Main St', $parking->getAddress());
        $this->assertEquals(1.23, $parking->getLatitude());
        $this->assertEquals(4.56, $parking->getLongitude());
        $this->assertEquals(100, $parking->getTotalCapacity());
        $this->assertTrue($parking->isOpen24_7());
    }
}


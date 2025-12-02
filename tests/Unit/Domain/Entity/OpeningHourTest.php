<?php

namespace Unit\Domain\Entity;

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\OpeningHour;

class OpeningHourTest extends TestCase
{
    public function testGetters()
    {
        $hour = new OpeningHour(1, 2, 1, new \DateTimeImmutable('08:00'), new \DateTimeImmutable('18:00'));
        $this->assertEquals(1, $hour->getOpeningHourId());
        $this->assertEquals(2, $hour->getParkingId());
        $this->assertEquals(1, $hour->getWeekday());
        $this->assertEquals(new \DateTimeImmutable('08:00'), $hour->getOpeningTime());
        $this->assertEquals(new \DateTimeImmutable('18:00'), $hour->getClosingTime());
    }
}

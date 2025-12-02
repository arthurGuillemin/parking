<?php

namespace Unit\Domain\Entity;

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\Reservation;

class ReservationTest extends TestCase
{
    public function testGetters()
    {
        $reservation = new Reservation(1, 'user-uuid', 2, new \DateTimeImmutable('2025-11-28 10:00:00'), new \DateTimeImmutable('2025-11-28 12:00:00'), 'active', 10.0, 12.0);
        $this->assertEquals(1, $reservation->getReservationId());
        $this->assertEquals('user-uuid', $reservation->getUserId());
        $this->assertEquals(2, $reservation->getParkingId());
        $this->assertEquals(new \DateTimeImmutable('2025-11-28 10:00:00'), $reservation->getStartDateTime());
        $this->assertEquals(new \DateTimeImmutable('2025-11-28 12:00:00'), $reservation->getEndDateTime());
        $this->assertEquals('active', $reservation->getStatus());
        $this->assertEquals(10.0, $reservation->getCalculatedAmount());
        $this->assertEquals(12.0, $reservation->getFinalAmount());
    }
}


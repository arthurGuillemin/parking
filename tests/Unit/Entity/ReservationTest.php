<?php

namespace Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\Reservation;
use DateTimeImmutable;

class ReservationTest extends TestCase
{
    public function testGetters()
    {
        $start = new DateTimeImmutable('2024-01-01 10:00:00');
        $end = new DateTimeImmutable('2024-01-01 12:00:00');

        $reservation = new Reservation(
            1,
            'user-123',
            5,
            $start,
            $end,
            'confirmed',
            10.0,
            12.0
        );

        $this->assertEquals(1, $reservation->getReservationId());
        $this->assertEquals('user-123', $reservation->getUserId());
        $this->assertEquals(5, $reservation->getParkingId());
        $this->assertEquals($start, $reservation->getStartDateTime());
        $this->assertEquals($end, $reservation->getEndDateTime());
        $this->assertEquals('confirmed', $reservation->getStatus());
        $this->assertEquals(10.0, $reservation->getCalculatedAmount());
        $this->assertEquals(12.0, $reservation->getFinalAmount());
    }
}

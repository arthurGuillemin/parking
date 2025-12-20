<?php

namespace Unit\Domain\Entity;

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\ParkingSession;

class ParkingSessionTest extends TestCase
{
    public function testGetters()
    {
        $session = new ParkingSession(1, 'user-uuid', 2, 3, new \DateTimeImmutable('2025-11-28 10:00:00'), new \DateTimeImmutable('2025-11-28 12:00:00'), 15.0, true);
        $this->assertEquals(1, $session->getSessionId());
        $this->assertEquals('user-uuid', $session->getUserId());
        $this->assertEquals(2, $session->getParkingId());
        $this->assertEquals(3, $session->getReservationId());
        $this->assertEquals(new \DateTimeImmutable('2025-11-28 10:00:00'), $session->getEntryDateTime());
        $this->assertEquals(new \DateTimeImmutable('2025-11-28 12:00:00'), $session->getExitDateTime());
        $this->assertEquals(15.0, $session->getFinalAmount());
        $this->assertTrue($session->isPenaltyApplied());
    }
}

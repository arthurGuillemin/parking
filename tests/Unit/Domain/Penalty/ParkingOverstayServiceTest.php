<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Penalty;

use App\Domain\Entity\ParkingSession;
use App\Domain\Penalty\ParkingOverstayService;
use App\Domain\ValueObject\TimeRange;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class ParkingOverstayServiceTest extends TestCase
{
    private function createSession(
        DateTimeImmutable $entry,
        ?DateTimeImmutable $exit
    ): ParkingSession {
        return new ParkingSession(
            id: 1,
            userId: 'user-uuid',
            parkingId: 42,
            reservationId: 100,
            entryDateTime: $entry,
            exitDateTime: $exit,
            finalAmount: null,
            penaltyApplied: false
        );
    }

    public function testNoOverstayWhenExitBeforeAuthorizedEnd(): void
    {
        $service = new ParkingOverstayService();

        $reservation = new TimeRange(
            new DateTimeImmutable('2025-01-01 10:00:00'),
            new DateTimeImmutable('2025-01-01 13:00:00')
        );

        $session = $this->createSession(
            new DateTimeImmutable('2025-01-01 10:05:00'),
            new DateTimeImmutable('2025-01-01 12:55:00')
        );

        $result = $service->evaluateOverstay($session, [$reservation]);

        $this->assertFalse($result->hasOverstay());
        $this->assertSame(0, $result->getOverstayMinutes());
        $this->assertSame(0, $result->getPenaltyAmountCents());
    }

    public function testOverstayAppliesFixedPenaltyAndCountsExtraMinutes(): void
    {
        $service = new ParkingOverstayService();

        $reservation = new TimeRange(
            new DateTimeImmutable('2025-01-01 10:00:00'),
            new DateTimeImmutable('2025-01-01 13:00:00')
        );

        // sortie à 14h -> 1h de dépassement
        $session = $this->createSession(
            new DateTimeImmutable('2025-01-01 10:00:00'),
            new DateTimeImmutable('2025-01-01 14:00:00')
        );

        $result = $service->evaluateOverstay($session, [$reservation]);

        $this->assertTrue($result->hasOverstay());
        $this->assertSame(60, $result->getOverstayMinutes());
        $this->assertSame(2000, $result->getPenaltyAmountCents()); // 20 euros
    }

    public function testUserStillInsideAfterEndIsConsideredOverstaying(): void
    {
        $service = new ParkingOverstayService();

        $reservation = new TimeRange(
            new DateTimeImmutable('2025-01-01 10:00:00'),
            new DateTimeImmutable('2025-01-01 13:00:00')
        );

        // Utilisateur pas encore sorti
        $session = $this->createSession(
            new DateTimeImmutable('2025-01-01 10:00:00'),
            null
        );

        // On se place à 14h
        $now = new DateTimeImmutable('2025-01-01 14:00:00');

        $result = $service->evaluateOverstay($session, [$reservation], $now);

        $this->assertTrue($result->hasOverstay());
        $this->assertSame(60, $result->getOverstayMinutes());
        $this->assertSame(2000, $result->getPenaltyAmountCents());
    }

    public function testMultipleAuthorizedRangesUseLatestEnd(): void
    {
        $service = new ParkingOverstayService();

        $range1 = new TimeRange(
            new DateTimeImmutable('2025-01-01 08:00:00'),
            new DateTimeImmutable('2025-01-01 10:00:00')
        );
        $range2 = new TimeRange(
            new DateTimeImmutable('2025-01-01 18:00:00'),
            new DateTimeImmutable('2025-01-01 22:00:00')
        );

        // Sortie à 23h -> 1h de dépassement après le dernier créneau
        $session = $this->createSession(
            new DateTimeImmutable('2025-01-01 19:00:00'),
            new DateTimeImmutable('2025-01-01 23:00:00')
        );

        $result = $service->evaluateOverstay($session, [$range1, $range2]);

        $this->assertTrue($result->hasOverstay());
        $this->assertSame(60, $result->getOverstayMinutes());
        $this->assertSame(2000, $result->getPenaltyAmountCents());
    }
}

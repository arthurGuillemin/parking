<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Pricing;

use App\Domain\Pricing\ReservationPriceCalculator;
use App\Domain\Pricing\TariffSlot;
use App\Domain\ValueObject\TimeRange;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class ReservationPriceCalculatorTest extends TestCase
{
    public function testFlatRateForSimpleReservation(): void
    {
        $calculator = new ReservationPriceCalculator();

        // Créneau : 10h -> 12h (2h)
        $range = new TimeRange(
            new DateTimeImmutable('2025-01-01 10:00:00'),
            new DateTimeImmutable('2025-01-01 12:00:00')
        );

        // Tarif unique : 2€ / heure (200 centimes), 24h/24
        $slot = new TariffSlot(
            startHour: 0,
            endHour: 0, // 0 => couvre 24h dans notre implémentation
            pricePerHourCents: 200
        );

        $priceCents = $calculator->calculate($range, [$slot]);

        // 2h * 2€ = 4€ => 400 centimes
        $this->assertSame(400, $priceCents);
    }

    public function testDifferentDayAndNightRates(): void
    {
        $calculator = new ReservationPriceCalculator();

        // Réservation de 19h à 22h (3h)
        $range = new TimeRange(
            new DateTimeImmutable('2025-01-01 19:00:00'),
            new DateTimeImmutable('2025-01-01 22:00:00')
        );

        // Jour : 8h–20h à 2€ / h
        $daySlot = new TariffSlot(
            startHour: 8,
            endHour: 20,
            pricePerHourCents: 200
        );

        // Nuit : 20h–8h à 1€ / h
        $nightSlot = new TariffSlot(
            startHour: 20,
            endHour: 8,
            pricePerHourCents: 100
        );

        $priceCents = $calculator->calculate($range, [$daySlot, $nightSlot]);

        // De 19h à 20h : 1h à 2€ = 200
        // De 20h à 22h : 2h à 1€ = 200
        // Total = 400 centimes
        $this->assertSame(400, $priceCents);
    }

    public function testWeekendOnlyTariff(): void
    {
        $calculator = new ReservationPriceCalculator();

        // Samedi 10h–12h
        $range = new TimeRange(
            new DateTimeImmutable('2025-01-04 10:00:00'), // 4 janv 2025 = samedi
            new DateTimeImmutable('2025-01-04 12:00:00')
        );

        // Semaine : 8h–20h à 2€ / h
        $weekdaySlot = new TariffSlot(
            startHour: 8,
            endHour: 20,
            pricePerHourCents: 200,
            weekendOnly: false,
            weekdaysOnly: true
        );

        // Week-end : 8h–20h à 3€ / h
        $weekendSlot = new TariffSlot(
            startHour: 8,
            endHour: 20,
            pricePerHourCents: 300,
            weekendOnly: true,
            weekdaysOnly: false
        );

        $priceCents = $calculator->calculate($range, [$weekdaySlot, $weekendSlot]);

        // 2h à 3€ = 6€ = 600 centimes
        $this->assertSame(600, $priceCents);
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Pricing;

use DateTimeImmutable;
use InvalidArgumentException;

final class TariffSlot
{
    public function __construct(
        private int $startHour,            // 0..23
        private int $endHour,              // 0..24 (exclu)
        private int $pricePerHourCents,    // ex: 250 = 2,50 €
        private bool $weekendOnly = false,
        private bool $weekdaysOnly = false
    ) {
        if ($startHour < 0 || $startHour > 23 || $endHour < 0 || $endHour > 24) {
            throw new InvalidArgumentException('startHour/endHour must be between 0 and 24.');
        }

        if ($weekendOnly && $weekdaysOnly) {
            throw new InvalidArgumentException('TariffSlot cannot be both weekendOnly and weekdaysOnly.');
        }

        if ($pricePerHourCents < 0) {
            throw new InvalidArgumentException('pricePerHourCents cannot be negative.');
        }
    }

    public function getPricePerHourCents(): int
    {
        return $this->pricePerHourCents;
    }

    public function appliesTo(DateTimeImmutable $instant): bool
    {
        $hour = (int) $instant->format('G'); // 0..23
        $dayOfWeek = (int) $instant->format('N'); // 1=Mon .. 7=Sun

        // Gestion semaine / week-end
        $isWeekend = $dayOfWeek >= 6;

        if ($this->weekendOnly && !$isWeekend) {
            return false;
        }

        if ($this->weekdaysOnly && $isWeekend) {
            return false;
        }

        // Gestion des créneaux horaires, y compris de nuit (ex: 20h–6h)
        if ($this->startHour < $this->endHour) {
            // créneau "normal" dans la journée : [startHour, endHour[
            return $hour >= $this->startHour && $hour < $this->endHour;
        }

        if ($this->startHour > $this->endHour) {
            // créneau "nuit" qui chevauche minuit, ex: 20h–6h
            return $hour >= $this->startHour || $hour < $this->endHour;
        }

        // startHour == endHour -> couvre 24h
        return true;
    }
}

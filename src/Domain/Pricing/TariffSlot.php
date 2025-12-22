<?php

declare(strict_types=1);

namespace App\Domain\Pricing;

use DateTimeImmutable;
use InvalidArgumentException;

final class TariffSlot
{
    public function __construct(
        private int $startHour,
        private int $endHour,
        private int $pricePerHourCents,
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
        $hour = (int) $instant->format('G');
        $dayOfWeek = (int) $instant->format('N');

        $isWeekend = $dayOfWeek >= 6;

        if ($this->weekendOnly && !$isWeekend) {
            return false;
        }

        if ($this->weekdaysOnly && $isWeekend) {
            return false;
        }

        if ($this->startHour < $this->endHour) {
            return $hour >= $this->startHour && $hour < $this->endHour;
        }

        if ($this->startHour > $this->endHour) {
            return $hour >= $this->startHour || $hour < $this->endHour;
        }

        return true;
    }
}

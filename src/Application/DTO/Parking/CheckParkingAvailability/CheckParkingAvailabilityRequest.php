<?php

namespace App\Application\DTO\Parking\CheckParkingAvailability;

readonly class CheckParkingAvailabilityRequest
{
    public function __construct(
        public int $parkingId,
        public \DateTimeImmutable $dateTime
    ) {}
}

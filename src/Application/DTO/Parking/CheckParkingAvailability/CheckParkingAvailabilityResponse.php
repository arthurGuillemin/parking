<?php

namespace App\Application\DTO\Parking\CheckParkingAvailability;

readonly class CheckParkingAvailabilityResponse
{
    public function __construct(
        public bool $available,
        public ?string $message = null
    ) {}
}

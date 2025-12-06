<?php

namespace App\Application\DTO\Parking\CountAvailableParkingSpots;

// DTO pour la requête de comptage des places de parking disponibles
readonly class CountAvailableParkingSpotsRequest
{
    public function __construct(
        public int $parkingId,
        public \DateTimeImmutable $at
    ) {}
}

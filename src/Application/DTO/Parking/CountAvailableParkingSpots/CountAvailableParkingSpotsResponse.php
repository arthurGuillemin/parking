<?php

namespace App\Application\DTO\Parking\CountAvailableParkingSpots;

// DTO pour la réponse du comptage des places de parking disponibles
readonly class CountAvailableParkingSpotsResponse
{
    public function __construct(
        public int $parkingId,
        public int $totalCapacity,
        public int $availableSpots,
        public \DateTimeImmutable $at
    ) {}
}

<?php

namespace App\Application\UseCase\Parking\CountAvailableParkingSpotsUseCase;

// DTO pour la requête de comptage des places de parking disponibles
class CountAvailableParkingSpotsRequest
{
    public int $parkingId;
    public \DateTimeImmutable $at;

    public function __construct(int $parkingId, \DateTimeImmutable $at)
    {
        $this->parkingId = $parkingId;
        $this->at = $at;
    }
}

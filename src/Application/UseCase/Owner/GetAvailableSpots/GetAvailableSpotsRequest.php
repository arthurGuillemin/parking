<?php

namespace App\Application\UseCase\Owner\GetAvailableSpots;

class GetAvailableSpotsRequest
{
    public int $parkingId;
    public \DateTimeImmutable $at;

    public function __construct(int $parkingId, \DateTimeImmutable $at)
    {
        $this->parkingId = $parkingId;
        $this->at = $at;
    }
}


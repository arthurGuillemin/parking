<?php

namespace App\Application\UseCase\User\ExitParking;

class ExitParkingRequest
{
    public string $userId;
    public int $parkingId;

    public function __construct(string $userId, int $parkingId)
    {
        $this->userId = $userId;
        $this->parkingId = $parkingId;
    }
}

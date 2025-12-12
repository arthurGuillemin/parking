<?php

namespace App\Application\UseCase\User\EnterParking;

class EnterParkingRequest
{
    public string $userId;
    public int $parkingId;

    public function __construct(string $userId, int $parkingId)
    {
        $this->userId = $userId;
        $this->parkingId = $parkingId;
    }
}

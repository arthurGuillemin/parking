<?php

namespace App\Application\UseCase\User\EnterParking;

class EnterParkingRequest
{
    public string $userId;
    public int $parkingId;
    public ?int $reservationId;

    public function __construct(string $userId, int $parkingId, ?int $reservationId = null)
    {
        $this->userId = $userId;
        $this->parkingId = $parkingId;
        $this->reservationId = $reservationId;
    }
}

<?php

namespace App\Application\UseCase\Owner\ListParkingSessions;

class ListParkingSessionsRequest
{
    public int $parkingId;

    public function __construct(int $parkingId)
    {
        $this->parkingId = $parkingId;
    }
}


<?php

namespace App\Application\UseCase\Owner\ListSessionsOutOfReservationOrSubscription;

class ListSessionsOutOfReservationOrSubscriptionRequest
{
    public int $parkingId;

    public function __construct(int $parkingId)
    {
        $this->parkingId = $parkingId;
    }
}


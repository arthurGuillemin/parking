<?php

namespace App\Application\UseCase\Owner\ListSubscriptionTypes;

class ListSubscriptionTypesRequest
{
    public ?int $parkingId;

    public function __construct(?int $parkingId = null)
    {
        $this->parkingId = $parkingId;
    }
}

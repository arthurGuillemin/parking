<?php

namespace App\Application\UseCase\Owner\ListSubscriptionSlots;

class ListSubscriptionSlotsRequest
{
    public int $typeId;

    public function __construct(int $typeId)
    {
        $this->typeId = $typeId;
    }
}

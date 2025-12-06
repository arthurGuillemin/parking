<?php

namespace App\Application\UseCase\Owner\DeleteSubscriptionSlot;

class DeleteSubscriptionSlotRequest
{
    public int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }
}

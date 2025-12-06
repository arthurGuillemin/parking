<?php

namespace App\Application\UseCase\User\CancelSubscription;

class CancelSubscriptionRequest
{
    public int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }
}

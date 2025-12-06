<?php

namespace App\Application\UseCase\User\GetSubscription;

class GetSubscriptionRequest
{
    public int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }
}

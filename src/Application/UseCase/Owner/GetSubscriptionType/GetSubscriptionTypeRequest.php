<?php

namespace App\Application\UseCase\Owner\GetSubscriptionType;

class GetSubscriptionTypeRequest
{
    public int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }
}

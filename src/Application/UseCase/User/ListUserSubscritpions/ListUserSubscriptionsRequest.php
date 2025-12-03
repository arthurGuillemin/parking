<?php

namespace App\Application\UseCase\User\ListUserSubscriptions;

class ListUserSubscriptionsRequest
{
    public string $userId;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }
}
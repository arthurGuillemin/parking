<?php

namespace App\Application\UseCase\User\ListUserSessions;

class ListUserSessionsRequest
{
    public string $userId;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }
}

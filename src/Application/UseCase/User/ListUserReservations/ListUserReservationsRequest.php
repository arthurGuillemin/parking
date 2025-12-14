<?php

namespace App\Application\UseCase\User\ListUserReservations;

class ListUserReservationsRequest
{
    public string $userId;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }
}

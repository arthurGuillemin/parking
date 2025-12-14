<?php

namespace App\Application\UseCase\User\ListUserInvoices;

class ListUserInvoicesRequest
{
    public string $userId;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }
}

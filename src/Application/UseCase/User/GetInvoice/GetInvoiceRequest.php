<?php

namespace App\Application\UseCase\User\GetInvoice;

class GetInvoiceRequest
{
    public int $invoiceId;
    public string $userId;

    public function __construct(int $invoiceId, string $userId)
    {
        $this->invoiceId = $invoiceId;
        $this->userId = $userId;
    }
}

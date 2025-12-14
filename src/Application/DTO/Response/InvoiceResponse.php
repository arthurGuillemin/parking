<?php

namespace App\Application\DTO\Response;

class InvoiceResponse
{
    public int $id;
    public string $issueDate;
    public float $amountTtc;
    public string $type;

    public function __construct(int $id, string $issueDate, float $amountTtc, string $type)
    {
        $this->id = $id;
        $this->issueDate = $issueDate;
        $this->amountTtc = $amountTtc;
        $this->type = $type;
    }
}

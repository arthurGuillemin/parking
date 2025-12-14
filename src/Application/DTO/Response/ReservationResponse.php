<?php

namespace App\Application\DTO\Response;

class ReservationResponse
{
    public int $id;
    public string $userId;
    public int $parkingId;
    public string $start;
    public string $end;
    public string $status;
    public ?float $price;

    public function __construct(
        int $id,
        string $userId,
        int $parkingId,
        string $start,
        string $end,
        string $status,
        ?float $price
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->parkingId = $parkingId;
        $this->start = $start;
        $this->end = $end;
        $this->status = $status;
        $this->price = $price;
    }
}

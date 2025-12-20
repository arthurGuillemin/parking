<?php

namespace App\Application\UseCase\Owner\AddSubscriptionType;

class AddSubscriptionTypeResponse
{
    public int $id;
    public int $parkingId;
    public string $name;
    public ?string $description;
    public float $monthlyPrice;

    public function __construct(
        int $id,
        int $parkingId,
        string $name,
        ?string $description,
        float $monthlyPrice
    ) {
        $this->id = $id;
        $this->parkingId = $parkingId;
        $this->name = $name;
        $this->description = $description;
        $this->monthlyPrice = $monthlyPrice;
    }
}

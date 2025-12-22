<?php

namespace App\Application\UseCase\Owner\AddSubscriptionType;

class AddSubscriptionTypeRequest
{
    public int $parkingId;
    public string $name;
    public ?string $description;
    public float $monthlyPrice;

    public function __construct(int $parkingId, string $name, ?string $description = null, float $monthlyPrice = 0.0)
    {
        $this->parkingId = $parkingId;
        $this->name = $name;
        $this->description = $description;
        $this->monthlyPrice = $monthlyPrice;
    }
}

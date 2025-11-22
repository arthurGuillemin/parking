<?php

namespace App\Application\UseCase\Owner\AddSubscriptionType;

class AddSubscriptionTypeRequest
{
    public int $parkingId;
    public string $name;
    public ?string $description;

    public function __construct(int $parkingId, string $name, ?string $description = null)
    {
        $this->parkingId = $parkingId;
        $this->name = $name;
        $this->description = $description;
    }
}

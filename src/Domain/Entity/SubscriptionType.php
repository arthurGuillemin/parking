<?php

namespace App\Domain\Entity;

class SubscriptionType
{
    private int $id;
    private int $parkingId; // Un type de subscription est lié à un parking
    private string $name;
    private ?string $description;
    private float $monthlyPrice;

    public function __construct(int $id, int $parkingId, string $name, ?string $description, float $monthlyPrice = 0.0)
    {
        $this->id = $id;
        $this->parkingId = $parkingId;
        $this->name = $name;
        $this->description = $description;
        $this->monthlyPrice = $monthlyPrice;
    }

    public function getSubscriptionTypeId(): int
    {
        return $this->id;
    }

    public function getParkingId(): int
    {
        return $this->parkingId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getMonthlyPrice(): float
    {
        return $this->monthlyPrice;
    }

}

<?php

namespace App\Domain\Entity;

class SubscriptionType {
    private int $id;
    private int $parkingId; // Un type de subscription est lié à un parking
    private string $name;
    private ?string $description;

    public function __construct(int $id, int $parkingId, string $name, ?string $description) {
        $this->id = $id;
        $this->parkingId = $parkingId;
        $this->name = $name;
        $this->description = $description;
    }

    public function getSubscriptionTypeId(): int {
        return $this->id;
    }

    public function getParkingId(): int {
        return $this->parkingId;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getDescription(): ?string {
        return $this->description;
    }
}

<?php

namespace App\Domain\Entity;

class Parking {
    private int $id;
    private string $ownerId; // UUID
    private string $name;
    private string $address;
    private float $latitude;
    private float $longitude;
    private int $totalCapacity;
    private bool $open_24_7; // false par défaut

    public function __construct(int $id, string $ownerId, string $name, string $address, float $latitude, float $longitude, int $totalCapacity, bool $open_24_7 = false) {
        $this->id = $id;
        $this->ownerId = $ownerId;
        $this->name = $name;
        $this->address = $address;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->totalCapacity = $totalCapacity;
        $this->open_24_7 = $open_24_7;
    }

    public function getParkingId(): int {
        return $this->id;
    }

    public function getOwnerId(): string {
        return $this->ownerId;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getAddress(): string {
        return $this->address;
    }

    public function getLatitude(): float {
        return $this->latitude;
    }

    public function getLongitude(): float {
        return $this->longitude;
    }

    public function getTotalCapacity(): int {
        return $this->totalCapacity;
    }

    public function isOpen24_7(): bool {
        return $this->open_24_7;
    }
}

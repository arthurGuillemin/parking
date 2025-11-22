<?php

namespace App\Application\UseCase\Owner\AddParking;

class AddParkingRequest
{
    public string $ownerId;
    public string $name;
    public string $address;
    public float $latitude;
    public float $longitude;
    public int $totalCapacity;
    public bool $open_24_7;

    public function __construct(string $ownerId, string $name, string $address, float $latitude, float $longitude, int $totalCapacity, bool $open_24_7 = false)
    {
        $this->ownerId = $ownerId;
        $this->name = $name;
        $this->address = $address;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->totalCapacity = $totalCapacity;
        $this->open_24_7 = $open_24_7;
    }
}

<?php

namespace App\Application\DTO\Response;

class ParkingSessionResponse
{
    public int $id;
    public int $parkingId;
    public string $vehiclePlate;
    public string $entryTime;
    public ?string $exitTime;
    public ?float $pricePaid;

    public function __construct(
        int $id,
        int $parkingId,
        string $vehiclePlate,
        string $entryTime,
        ?string $exitTime,
        ?float $pricePaid
    ) {
        $this->id = $id;
        $this->parkingId = $parkingId;
        $this->vehiclePlate = $vehiclePlate;
        $this->entryTime = $entryTime;
        $this->exitTime = $exitTime;
        $this->pricePaid = $pricePaid;
    }
}

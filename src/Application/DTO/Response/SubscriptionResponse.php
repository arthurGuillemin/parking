<?php

namespace App\Application\DTO\Response;

class SubscriptionResponse
{
    public int $id;
    public string $userId;
    public int $parkingId;
    public ?int $typeId;
    public string $startDate;
    public ?string $endDate;
    public string $status;
    public float $monthlyPrice;
    public ?string $parkingName;

    public function __construct(
        int $id,
        string $userId,
        int $parkingId,
        ?int $typeId,
        string $startDate,
        ?string $endDate,
        string $status,
        float $monthlyPrice,
        ?string $parkingName = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->parkingId = $parkingId;
        $this->typeId = $typeId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->status = $status;
        $this->monthlyPrice = $monthlyPrice;
        $this->parkingName = $parkingName;
    }
}

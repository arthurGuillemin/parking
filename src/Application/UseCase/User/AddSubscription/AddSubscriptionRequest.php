<?php

namespace App\Application\UseCase\User\AddSubscription;

class AddSubscriptionRequest
{
    public string $userId;
    public int $parkingId;
    public ?int $typeId; // null = accès 24/7
    public \DateTimeImmutable $startDate;
    public ?\DateTimeImmutable $endDate;
    public float $monthlyPrice;

    public function __construct(
        string $userId,
        int $parkingId,
        ?int $typeId,
        \DateTimeImmutable $startDate,
        ?\DateTimeImmutable $endDate,
        float $monthlyPrice
    ) {
        $this->userId = $userId;
        $this->parkingId = $parkingId;
        $this->typeId = $typeId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->monthlyPrice = $monthlyPrice;
    }
}
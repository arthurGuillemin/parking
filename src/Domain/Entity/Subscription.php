<?php

namespace App\Domain\Entity;

class Subscription {
    private int $id;
    private string $userId; // UUID
    private int $parkingId;
    private ?int $typeId;
    private \DateTimeImmutable $startDate; // minimum 1 mois
    private ?\DateTimeImmutable $endDate; // max 1 an
    private string $status; // e.g., 'active', 'expired', 'cancelled'
    private float $monthlyPrice;

    public function __construct(int $id, string $userId, int $parkingId, ?int $typeId, \DateTimeImmutable $startDate, ?\DateTimeImmutable $endDate, string $status, float $monthlyPrice) {
        $this->id = $id;
        $this->userId = $userId;
        $this->parkingId = $parkingId;
        $this->typeId = $typeId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->status = $status;
        $this->monthlyPrice = $monthlyPrice;
    }

    public function getSubscriptionId(): int {
        return $this->id;
    }

    public function getUserId(): string {
        return $this->userId;
    }

    public function getParkingId(): int {
        return $this->parkingId;
    }

    public function getTypeId(): ?int {
        return $this->typeId;
    }

    public function getStartDate(): \DateTimeImmutable {
        return $this->startDate;
    }

    public function getEndDate(): ?\DateTimeImmutable {
        return $this->endDate;
    }

    public function getStatus(): string {
        return $this->status;
    }

    public function getMonthlyPrice(): float {
        return $this->monthlyPrice;
    }
}

<?php

namespace App\Domain\Entity;

class Reservation
{
    private int $id;
    private string $userId; // UUID
    private int $parkingId;
    private \DateTimeImmutable $startDateTime;
    private \DateTimeImmutable $endDateTime;
    private string $status;
    private ?float $calculatedAmount; // calculé à la création
    private ?float $finalAmount; // mis à jour après le stationnement

    public function __construct(int $id, string $userId, int $parkingId, \DateTimeImmutable $startDateTime, \DateTimeImmutable $endDateTime, string $status, ?float $calculatedAmount, ?float $finalAmount)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->parkingId = $parkingId;
        $this->startDateTime = $startDateTime;
        $this->endDateTime = $endDateTime;
        $this->status = $status;
        $this->calculatedAmount = $calculatedAmount;
        $this->finalAmount = $finalAmount;
    }

    public function getReservationId(): int
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getParkingId(): int
    {
        return $this->parkingId;
    }

    public function getStartDateTime(): \DateTimeImmutable
    {
        return $this->startDateTime;
    }

    public function getEndDateTime(): \DateTimeImmutable
    {
        return $this->endDateTime;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCalculatedAmount(): ?float
    {
        return $this->calculatedAmount;
    }

    public function getFinalAmount(): ?float
    {
        return $this->finalAmount;
    }

    public function complete(\DateTimeImmutable $endTime, float $amount): void
    {
        $this->status = 'completed';
        $this->endDateTime = $endTime;
        $this->finalAmount = $amount;
    }
}

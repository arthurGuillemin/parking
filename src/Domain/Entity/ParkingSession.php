<?php

namespace App\Domain\Entity;

class ParkingSession
{
    private int $id;
    private string $userId; // UUID
    private int $parkingId;
    private ?int $reservationId;
    private \DateTimeImmutable $entryDateTime;
    private ?\DateTimeImmutable $exitDateTime; // null si en cours
    private ?float $finalAmount; // null si en cours
    private bool $penaltyApplied; // false par défaut

    public function __construct(int $id, string $userId, int $parkingId, ?int $reservationId, \DateTimeImmutable $entryDateTime, ?\DateTimeImmutable $exitDateTime, ?float $finalAmount, bool $penaltyApplied)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->parkingId = $parkingId;
        $this->reservationId = $reservationId;
        $this->entryDateTime = $entryDateTime;
        $this->exitDateTime = $exitDateTime;
        $this->finalAmount = $finalAmount;
        $this->penaltyApplied = $penaltyApplied;
    }

    public function getSessionId(): int
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

    public function getReservationId(): ?int
    {
        return $this->reservationId;
    }

    public function getEntryDateTime(): \DateTimeImmutable
    {
        return $this->entryDateTime;
    }

    public function getExitDateTime(): ?\DateTimeImmutable
    {
        return $this->exitDateTime;
    }

    public function getFinalAmount(): ?float
    {
        return $this->finalAmount;
    }

    public function isPenaltyApplied(): bool
    {
        return $this->penaltyApplied;
    }

    public function close(\DateTimeImmutable $exitTime, float $amount): void
    {
        $this->exitDateTime = $exitTime;
        $this->finalAmount = $amount;
    }

    public function setExitDateTime(\DateTimeImmutable $exitDateTime): void
    {
        $this->exitDateTime = $exitDateTime;
    }

    public function setFinalAmount(float $amount): void
    {
        $this->finalAmount = $amount;
    }

    public function setPenaltyApplied(bool $applied): void
    {
        $this->penaltyApplied = $applied;
    }
}

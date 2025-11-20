<?php

namespace App\Domain\Entity;

class OpeningHour {
    private int $id;
    private int $parkingId;
    private int $weekday; // 1 (Lundi) à 7 (Dimanche)
    private \DateTimeImmutable $openingTime; // format 'HH:MM:SS'
    private \DateTimeImmutable $closingTime; // format 'HH:MM:SS'

    public function __construct(int $id, int $parkingId, int $weekday, \DateTimeImmutable $openingTime, \DateTimeImmutable $closingTime) {
        $this->id = $id;
        $this->parkingId = $parkingId;
        $this->weekday = $weekday;
        $this->openingTime = $openingTime;
        $this->closingTime = $closingTime;
    }

    public function getOpeningHourId(): int {
        return $this->id;
    }

    public function getParkingId(): int {
        return $this->parkingId;
    }

    public function getWeekday(): int {
        return $this->weekday;
    }

    public function getOpeningTime(): \DateTimeImmutable {
        return $this->openingTime;
    }

    public function getClosingTime(): \DateTimeImmutable {
        return $this->closingTime;
    }
}

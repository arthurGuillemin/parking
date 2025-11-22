<?php

namespace App\Application\UseCase\Owner\UpdateOpeningHour;

class UpdateOpeningHourRequest
{
    public int $parkingId;
    public int $weekday;
    public string $openingTime; // format 'HH:MM:SS'
    public string $closingTime; // format 'HH:MM:SS'

    public function __construct(int $parkingId, int $weekday, string $openingTime, string $closingTime)
    {
        $this->parkingId = $parkingId;
        $this->weekday = $weekday;
        $this->openingTime = $openingTime;
        $this->closingTime = $closingTime;
    }
}


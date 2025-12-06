<?php

namespace App\Application\UseCase\Owner\UpdateOpeningHour;

class UpdateOpeningHourRequest
{
    public int $parkingId;
    public int $weekdayStart;
    public int $weekdayEnd;
    public string $openingTime; // format 'HH:MM:SS'
    public string $closingTime; // format 'HH:MM:SS'

    public function __construct(int $parkingId, int $weekdayStart, int $weekdayEnd, string $openingTime, string $closingTime)
    {
        $this->parkingId = $parkingId;
        $this->weekdayStart = $weekdayStart;
        $this->weekdayEnd = $weekdayEnd;
        $this->openingTime = $openingTime;
        $this->closingTime = $closingTime;
    }
}

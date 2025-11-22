<?php

namespace App\Application\UseCase\Owner\GetMonthlyRevenue;

class GetMonthlyRevenueRequest
{
    public int $parkingId;
    public int $year;
    public int $month;

    public function __construct(int $parkingId, int $year, int $month)
    {
        $this->parkingId = $parkingId;
        $this->year = $year;
        $this->month = $month;
    }
}


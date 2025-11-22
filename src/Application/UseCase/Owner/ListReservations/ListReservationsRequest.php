<?php

namespace App\Application\UseCase\Owner\ListReservations;

class ListReservationsRequest
{
    public int $parkingId;
    public ?\DateTimeImmutable $start;
    public ?\DateTimeImmutable $end;

    public function __construct(int $parkingId, ?\DateTimeImmutable $start = null, ?\DateTimeImmutable $end = null)
    {
        $this->parkingId = $parkingId;
        $this->start = $start;
        $this->end = $end;
    }
}


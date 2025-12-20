<?php

namespace App\Application\UseCase\User\MakeReservation;

class MakeReservationRequest
{
    public string $userId;
    public int $parkingId;
    public \DateTimeImmutable $startDateTime;
    public \DateTimeImmutable $endDateTime;

    public function __construct(string $userId, int $parkingId, \DateTimeImmutable $startDateTime, \DateTimeImmutable $endDateTime)
    {
        $this->userId = $userId;
        $this->parkingId = $parkingId;
        $this->startDateTime = $startDateTime;
        $this->endDateTime = $endDateTime;
    }
}

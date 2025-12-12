<?php

namespace App\Application\DTO\Response;

use App\Domain\Entity\Reservation;

class ReservationResponse
{
    public int $id;
    public string $userId;
    public int $parkingId;
    public string $startDateTime;
    public string $endDateTime;
    public string $status;
    public ?float $amount;

    public function __construct(Reservation $reservation)
    {
        $this->id = $reservation->getReservationId();
        $this->userId = $reservation->getUserId();
        $this->parkingId = $reservation->getParkingId();
        $this->startDateTime = $reservation->getStartDateTime()->format(\DateTimeInterface::ATOM);
        $this->endDateTime = $reservation->getEndDateTime()->format(\DateTimeInterface::ATOM);
        $this->status = $reservation->getStatus();
        $this->amount = $reservation->getCalculatedAmount();
    }
}

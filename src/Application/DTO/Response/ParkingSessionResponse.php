<?php

namespace App\Application\DTO\Response;

use App\Domain\Entity\ParkingSession;

class ParkingSessionResponse
{
    public int $id;
    public string $userId;
    public int $parkingId;
    public ?int $reservationId;
    public string $entryDateTime;
    public ?string $exitDateTime;
    public ?float $amount;
    public bool $penaltyApplied;

    public ?string $parkingName;

    public function __construct(ParkingSession $session, ?string $parkingName = null)
    {
        $this->id = $session->getSessionId();
        $this->userId = $session->getUserId();
        $this->parkingId = $session->getParkingId();
        $this->reservationId = $session->getReservationId();
        $this->entryDateTime = $session->getEntryDateTime()->format(\DateTimeInterface::ATOM);
        $this->exitDateTime = $session->getExitDateTime() ? $session->getExitDateTime()->format(\DateTimeInterface::ATOM) : null;
        $this->amount = $session->getFinalAmount();
        $this->penaltyApplied = $session->isPenaltyApplied();
        $this->parkingName = $parkingName;
    }
}

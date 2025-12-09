<?php

declare(strict_types=1);

namespace App\Application\UseCase\Parking;

final class ExitParkingResult
{
    public function __construct(
        private int $sessionId,
        private int $basePriceCents,
        private int $penaltyCents,
        private int $totalCents
    ) {}

    public function getSessionId(): int
    {
        return $this->sessionId;
    }

    public function getBasePriceCents(): int
    {
        return $this->basePriceCents;
    }

    public function getPenaltyCents(): int
    {
        return $this->penaltyCents;
    }

    public function getTotalCents(): int
    {
        return $this->totalCents;
    }
}

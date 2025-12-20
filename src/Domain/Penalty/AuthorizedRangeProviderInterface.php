<?php

declare(strict_types=1);

namespace App\Domain\Penalty;

use App\Domain\Entity\ParkingSession;
use App\Domain\ValueObject\TimeRange;

interface AuthorizedRangeProviderInterface
{
    /**
     * Retourne tous les créneaux autorisés (réservation + abonnements)
     * pour une session donnée.
     *
     * @return TimeRange[]
     */
    public function getAuthorizedRangesForSession(ParkingSession $session): array;
}

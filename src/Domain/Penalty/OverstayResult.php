<?php

declare(strict_types=1);

namespace App\Domain\Penalty;

/**
 * Résultat métier du calcul de dépassement :
 * - présence ou non de dépassement
 * - durée du dépassement en minutes
 * - montant de pénalité (en centimes pour éviter les problèmes de float)
 */
final class OverstayResult
{
    public function __construct(
        private bool $hasOverstay,
        private int $overstayMinutes,
        private int $penaltyAmountCents
    ) {
    }

    public function hasOverstay(): bool
    {
        return $this->hasOverstay;
    }

    public function getOverstayMinutes(): int
    {
        return $this->overstayMinutes;
    }

    public function getPenaltyAmountCents(): int
    {
        return $this->penaltyAmountCents;
    }
}

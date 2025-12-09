<?php

declare(strict_types=1);

namespace App\Domain\Penalty;

use App\Domain\Entity\ParkingSession;
use App\Domain\ValueObject\TimeRange;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Service de domaine chargé de déterminer si un stationnement a dépassé
 * ses créneaux autorisés (réservation / abonnement) et quelle pénalité appliquer.
 *
 * ça ne calcule pas le prix de la réservation de base, seulement :
 *  - la durée du dépassement
 *  - le montant fixe de la pénalité (20€) le cas échéant
 */
final class ParkingOverstayService
{
    private const PENALTY_AMOUNT_CENTS = 2000; // 20 €

    /**
     * @param TimeRange[] $authorizedRanges  Liste des créneaux autorisés (réservation + abonnements)
     * @param ParkingSession $session        Stationnement (entrée / sortie )
     * @param DateTimeImmutable|null $now    Moment de référence si l'utilisateur est encore garé
     */
    public function evaluateOverstay(
        ParkingSession $session,
        array $authorizedRanges,
        ?DateTimeImmutable $now = null
    ): OverstayResult {
        if (empty($authorizedRanges)) {
            throw new InvalidArgumentException('At least one authorized time range is required.');
        }

        $now ??= new DateTimeImmutable();

        // Si l'utilisateur n'est pas encore sorti, on prend "now" comme fin
        $end = $session->getExitDateTime() ?? $now;

        // Fin du dernier créneau autorisé
        $latestEnd = $authorizedRanges[0]->getEnd();
        foreach ($authorizedRanges as $range) {
            if ($range->getEnd() > $latestEnd) {
                $latestEnd = $range->getEnd();
            }
        }

        // Pas de dépassement si l'heure de sortie est <= fin du dernier créneau
        if ($end <= $latestEnd) {
            return new OverstayResult(
                hasOverstay: false,
                overstayMinutes: 0,
                penaltyAmountCents: 0
            );
        }

        // Dépassement : durée en minutes
        $seconds = $end->getTimestamp() - $latestEnd->getTimestamp();
        $overstayMinutes = (int) ceil($seconds / 60);

        return new OverstayResult(
            hasOverstay: true,
            overstayMinutes: $overstayMinutes,
            penaltyAmountCents: self::PENALTY_AMOUNT_CENTS
        );
    }
}

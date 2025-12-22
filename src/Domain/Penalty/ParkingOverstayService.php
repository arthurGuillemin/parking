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
     * Évalue si un stationnement a dépassé ses créneaux autorisés.
     *
     * @param ParkingSession $session Session de stationnement
     * @param TimeRange[] $authorizedRanges Créneaux autorisés
     * @param DateTimeImmutable|null $now Moment de référence
     */
    public function evaluateOverstay(
        ParkingSession $session,
        array $authorizedRanges,
        ?DateTimeImmutable $now = null
    ): OverstayResult {
        $this->validateAuthorizedRanges($authorizedRanges);

        $now ??= new DateTimeImmutable();
        $exitTime = $this->getEffectiveExitTime($session, $now);
        $latestAuthorizedEnd = $this->findLatestAuthorizedEnd($authorizedRanges);

        return $this->buildOverstayResult($exitTime, $latestAuthorizedEnd);
    }

    /**
     * Valide que la liste des créneaux autorisés n'est pas vide.
     */
    private function validateAuthorizedRanges(array $authorizedRanges): void
    {
        if (empty($authorizedRanges)) {
            throw new InvalidArgumentException('At least one authorized time range is required.');
        }
    }

    /**
     * Retourne l'heure de sortie effective (sortie réelle ou "now" si encore garé).
     */
    private function getEffectiveExitTime(ParkingSession $session, DateTimeImmutable $now): DateTimeImmutable
    {
        return $session->getExitDateTime() ?? $now;
    }

    /**
     * Trouve la fin du dernier créneau autorisé.
     */
    private function findLatestAuthorizedEnd(array $authorizedRanges): DateTimeImmutable
    {
        $latestEnd = $authorizedRanges[0]->getEnd();

        foreach ($authorizedRanges as $range) {
            if ($range->getEnd() > $latestEnd) {
                $latestEnd = $range->getEnd();
            }
        }

        return $latestEnd;
    }

    /**
     * Construit le résultat de dépassement basé sur la comparaison des temps.
     */
    private function buildOverstayResult(
        DateTimeImmutable $exitTime,
        DateTimeImmutable $latestAuthorizedEnd
    ): OverstayResult {
        if ($exitTime <= $latestAuthorizedEnd) {
            return new OverstayResult(
                hasOverstay: false,
                overstayMinutes: 0,
                penaltyAmountCents: 0
            );
        }

        $overstayMinutes = $this->calculateOverstayMinutes($exitTime, $latestAuthorizedEnd);

        return new OverstayResult(
            hasOverstay: true,
            overstayMinutes: $overstayMinutes,
            penaltyAmountCents: self::PENALTY_AMOUNT_CENTS
        );
    }

    /**
     * Calcule la durée du dépassement en minutes.
     */
    private function calculateOverstayMinutes(
        DateTimeImmutable $exitTime,
        DateTimeImmutable $latestAuthorizedEnd
    ): int {
        $seconds = $exitTime->getTimestamp() - $latestAuthorizedEnd->getTimestamp();

        return (int) ceil($seconds / 60);
    }
}

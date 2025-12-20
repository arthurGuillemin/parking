<?php

declare(strict_types=1);

namespace App\Domain\Pricing;

use App\Domain\ValueObject\TimeRange;
use DateTimeImmutable;
use RuntimeException;

final class ReservationPriceCalculator
{
    /**
     * Calcule le prix d'une réservation en fonction d'un créneau
     * et d'une liste de règles tarifaires.
     *
     * @param TimeRange   $reservationRange Créneau de la réservation
     * @param TariffSlot[] $tariffSlots     Liste des règles tarifaires applicables
     *
     * @return int Prix total en centimes
     */
    public function calculate(TimeRange $reservationRange, array $tariffSlots): int
    {
        if (empty($tariffSlots)) {
            throw new RuntimeException('At least one TariffSlot is required to calculate price.');
        }

        $current = $reservationRange->getStart();
        $end     = $reservationRange->getEnd();

        // On travaille en "centimes" mais en divisant le prix/heure par 60 -> float
        $totalCents = 0.0;

        while ($current < $end) {
            $slot = $this->findApplicableSlot($current, $tariffSlots);

            if ($slot === null) {
                throw new RuntimeException(
                    sprintf('No tariff slot applies to %s', $current->format(DATE_ATOM))
                );
            }

            // Prix d'une minute = prix/heure / 60
            $totalCents += $slot->getPricePerHourCents() / 60;

            // Minute suivante
            $current = $current->modify('+1 minute');
        }

        return (int) round($totalCents);
    }

    /**
     * @param DateTimeImmutable $instant
     * @param TariffSlot[]      $tariffSlots
     */
    private function findApplicableSlot(DateTimeImmutable $instant, array $tariffSlots): ?TariffSlot
    {
        foreach ($tariffSlots as $slot) {
            if ($slot->appliesTo($instant)) {
                return $slot;
            }
        }

        return null;
    }
}

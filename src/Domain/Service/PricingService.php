<?php

namespace App\Domain\Service;

use App\Domain\Repository\PricingRuleRepositoryInterface;
use App\Domain\Entity\Parking;

class PricingService
{
    private PricingRuleRepositoryInterface $pricingRuleRepository;

    public function __construct(PricingRuleRepositoryInterface $pricingRuleRepository)
    {
        $this->pricingRuleRepository = $pricingRuleRepository;
    }

    /**
     * Calcule le prix total pour une durée de stationnement.
     */
    public function calculatePrice(int $parkingId, \DateInterval $duration, \DateTimeImmutable $atDate): float
    {
        $allRules = $this->pricingRuleRepository->findByParkingId($parkingId);
        $activeRules = $this->getActiveRules($allRules, $atDate);
        $totalMinutes = $this->convertDurationToMinutes($duration);

        $totalPrice = 0.0;
        foreach ($activeRules as $rule) {
            $totalPrice += $this->calculateRulePrice($rule, $totalMinutes);
        }

        return $totalPrice;
    }

    /**
     * Filtre et déduplique les règles pour ne garder que les plus récentes par intervalle.
     */
    private function getActiveRules(array $allRules, \DateTimeImmutable $atDate): array
    {
        $validRules = array_filter($allRules, fn($rule) => $rule->getEffectiveDate() <= $atDate);

        $activeRules = [];
        foreach ($validRules as $rule) {
            $key = $rule->getStartDurationMinute() . '-' . ($rule->getEndDurationMinute() ?? 'INF');
            $isMoreRecent = isset($activeRules[$key])
                && $rule->getEffectiveDate() > $activeRules[$key]->getEffectiveDate();

            if (!isset($activeRules[$key]) || $isMoreRecent) {
                $activeRules[$key] = $rule;
            }
        }

        return $activeRules;
    }

    /**
     * Convertit un DateInterval en minutes totales.
     */
    private function convertDurationToMinutes(\DateInterval $duration): int
    {
        return ($duration->days * 24 * 60) + ($duration->h * 60) + $duration->i;
    }

    /**
     * Calcule le prix pour une règle de tarification donnée.
     */
    private function calculateRulePrice(object $rule, int $totalMinutes): float
    {
        $startMinute = $rule->getStartDurationMinute();
        $endMinute = $rule->getEndDurationMinute();

        if ($totalMinutes < $startMinute) {
            return 0.0;
        }

        $effectiveEnd = ($endMinute === null) ? $totalMinutes : min($totalMinutes, $endMinute);
        $durationInRule = $effectiveEnd - $startMinute;

        if ($durationInRule <= 0) {
            return 0.0;
        }

        // "Tout quart d'heure entamé est dû" => ceil
        $slicesCount = ceil($durationInRule / $rule->getSliceInMinutes());

        return $slicesCount * $rule->getPricePerSlice();
    }
}

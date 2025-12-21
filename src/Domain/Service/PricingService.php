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

    public function calculatePrice(int $parkingId, \DateInterval $duration, \DateTimeImmutable $atDate): float
    {
        $allRules = $this->pricingRuleRepository->findByParkingId($parkingId);

        // Filter valid rules based on effective date
        $validRules = array_filter($allRules, fn($r) => $r->getEffectiveDate() <= $atDate);

        // Deduplicate: Keep only the most recent rule for each specific [start, end] range definition
        // Key concept: The "Definition" of the tier is the [start, end] interval.
        $activeRules = [];
        foreach ($validRules as $rule) {
            $key = $rule->getStartDurationMinute() . '-' . ($rule->getEndDurationMinute() ?? 'INF');

            if (!isset($activeRules[$key])) {
                $activeRules[$key] = $rule;
            } else {
                // If we already have a rule for this range, check if this one is more recent
                if ($rule->getEffectiveDate() > $activeRules[$key]->getEffectiveDate()) {
                    $activeRules[$key] = $rule;
                }
            }
        }

        // Convert total duration to minutes
        $totalMinutes = ($duration->days * 24 * 60) + ($duration->h * 60) + $duration->i;

        $price = 0.0;

        foreach ($activeRules as $rule) {
            $start = $rule->getStartDurationMinute();
            $end = $rule->getEndDurationMinute();

            // Logic: Determine duration overlap with this rule
            if ($totalMinutes < $start) {
                continue;
            }

            $effectiveEnd = ($end === null) ? $totalMinutes : min($totalMinutes, $end);
            $durationInRule = $effectiveEnd - $start;

            if ($durationInRule <= 0)
                continue;

            // Calculate slices
            // "Tout quart d'heure entamé est dû" => ceil
            $sliceSize = $rule->getSliceInMinutes();
            $slicesCount = ceil($durationInRule / $sliceSize);

            $rulePrice = $slicesCount * $rule->getPricePerSlice();
            $price += $rulePrice;
        }

        return $price;
    }
}

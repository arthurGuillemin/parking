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

    public function calculatePrice(int $parkingId, \DateInterval $duration): float
    {
        $rules = $this->pricingRuleRepository->findByParkingId($parkingId);

        // Convert total duration to minutes
        $totalMinutes = ($duration->days * 24 * 60) + ($duration->h * 60) + $duration->i;

        $price = 0.0;
        $minutesAccountedFor = 0;

        // Sort rules by start duration to ensure logical progression (optional but good practice)
        // usort($rules, fn($a, $b) => $a->getStartDurationMinute() <=> $b->getStartDurationMinute());

        // We need to calculate price based on "slices" that fit into duration.
        // Assuming rules cover ranges like "0 to 60min", "60 to 120min", etc.
        // Or "0 to infinity".

        foreach ($rules as $rule) {
            $start = $rule->getStartDurationMinute();
            $end = $rule->getEndDurationMinute(); // can be null (infinity)

            // Determine how many minutes of the total duration fall into THIS rule's range.
            // Range for this rule: [start, end]
            // We only care about minutes > start.

            // If totalMinutes is less than start, this rule doesn't apply (yet).
            if ($totalMinutes < $start) {
                continue;
            }

            // Effective end for this calculation step.
            // If rule end is null, it goes up to totalMinutes.
            // If rule end is defined, it caps at rule end OR totalMinutes, whichever is smaller.
            $effectiveEnd = ($end === null) ? $totalMinutes : min($totalMinutes, $end);

            // Duration within this rule's bracket
            $durationInRule = $effectiveEnd - $start;

            if ($durationInRule <= 0)
                continue;

            // Calculate slices
            // "pricePerSlice" for every "sliceInMinutes".
            // Typically: ceil(duration / slice) * price? or pure division?
            // "Tout quart d'heure entamé est dû" => ceil.
            $sliceSize = $rule->getSliceInMinutes();
            $slicesCount = ceil($durationInRule / $sliceSize);

            $rulePrice = $slicesCount * $rule->getPricePerSlice();

            // PROBLEM: This logic assumes "cumulative" tiers (0-60 is one tier, 60-120 is another).
            // Does the pricing structure imply cumulative (like tax brackets) or "If duration is X, apply rule Y"?
            // "Un conducteur... paye comme une réservation de 4h".
            // Usually parking is cumulative. "First hour X, then Y per hour".
            // Let's assume cumulative logic based on `startDurationMinute` and `endDurationMinute`.
            // Wait, if I have a rule 0-60min and 60-120min.
            // If I stay 90min.
            // Do I pay (60min rule) + (30min rule)?
            // Or does "90min" fall into a generic "60-120" bracket that overrides 0-60?
            // Standard parking logic is cumulative.

            // However, the `PricingRule` structure has `start` and `end`.
            // Let's look at how "0 to infinity" is handled.
            // If we have disjoint ranges, it's cumulative.

            // Let's implement simpler logic found in typical parking apps if ambiguous: 
            // Sum of parts.
            // BUT, verifying `minutesAccountedFor` is tricky if rules overlap or gap.
            // Let's assume rules are distinct layers covering the timeline.

            // Let's rely on finding ALL applicable rules and summing them up?
            // User requirement: "Un parking peut être disponible...".
            // Actually, let's assume one rule applies for the specific duration? No, usually complex.
            // Given "start" and "end", it suggests tiers.

            // Let's assume cumulative.
            // I need to only charge for the portion of time in this bracket.
            // BUT: If rule 1 is 0-60 (2€/15min) and rule 2 is 60+ (1€/15min).
            // Stay 70min.
            // 0-60: 4 slices * 2€ = 8€.
            // 60-70: 10min -> 1 slice * 1€ = 1€. Total 9€.

            // However, $durationInRule calculation above:
            // if rule is 60-infinity. start=60. effectiveEnd=70. durationInRule=10. Correct.

            // One catch: Prorata?
            // Usually "slices" reset at the boundary? Or carry over?
            // "Tout quart d'heure est dû".
            // If I stay 60min (exact match of rule 1 end).
            // Then 0min in rule 2.
            // If I stay 61min. 1min in rule 2.

            // Current logic:
            // Loop through all rules. Accumulate cost.
            $price += $rulePrice;
        }

        return $price;
    }
}

<?php

namespace App\Application\UseCase\Owner\UpdatePricingRule;

use App\Domain\Repository\PricingRuleRepositoryInterface;
use App\Domain\Entity\PricingRule;

class UpdatePricingRuleUseCase
{
    private PricingRuleRepositoryInterface $pricingRuleRepository;

    public function __construct(PricingRuleRepositoryInterface $pricingRuleRepository)
    {
        $this->pricingRuleRepository = $pricingRuleRepository;
    }

    /**
     * Update or add a pricing rule for a parking.
     *
     * @param UpdatePricingRuleRequest $request
     * @return PricingRule
     */
    public function execute(UpdatePricingRuleRequest $request): PricingRule
    {
        // On crée une nouvelle règle tarifaire (historisation par date d'effet)
        $pricingRule = new PricingRule(
            0, // id auto-incrémenté par la DB
            $request->parkingId,
            $request->startDurationMinute,
            $request->endDurationMinute,
            $request->pricePerSlice,
            $request->sliceInMinutes,
            $request->effectiveDate
        );
        return $this->pricingRuleRepository->save($pricingRule);
    }
}


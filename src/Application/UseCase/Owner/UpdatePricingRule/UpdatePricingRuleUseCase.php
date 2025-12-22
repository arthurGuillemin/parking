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
     * Mettre à jour ou ajouter une règle tarifaire pour un parking.
     *
     * @param UpdatePricingRuleRequest $request
     * @return PricingRule
     */
    public function execute(UpdatePricingRuleRequest $request): PricingRule
    {
        // Créer une nouvelle règle tarifaire (historisation par date d'effet)
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


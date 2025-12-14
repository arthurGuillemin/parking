<?php

namespace App\Domain\Service;

use App\Domain\Repository\PricingRuleRepositoryInterface;
use App\Application\UseCase\Owner\UpdatePricingRule\UpdatePricingRuleUseCase;
use App\Application\UseCase\Owner\UpdatePricingRule\UpdatePricingRuleRequest;
use App\Domain\Entity\PricingRule;

class PricingRuleService
{
    private PricingRuleRepositoryInterface $pricingRuleRepository;
    private UpdatePricingRuleUseCase $updatePricingRuleUseCase;

    public function __construct(PricingRuleRepositoryInterface $pricingRuleRepository)
    {
        $this->pricingRuleRepository = $pricingRuleRepository;
        $this->updatePricingRuleUseCase = new UpdatePricingRuleUseCase($pricingRuleRepository);
    }

    public function updatePricingRule(UpdatePricingRuleRequest $request): PricingRule
    {
        return $this->updatePricingRuleUseCase->execute($request);
    }

    public function getPricingRulesByParkingId(int $parkingId): array
    {
        return $this->pricingRuleRepository->findByParkingId($parkingId);
    }
}


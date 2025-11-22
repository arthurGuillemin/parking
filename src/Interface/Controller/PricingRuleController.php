<?php

namespace App\Interface\Controller;

use App\Domain\Service\PricingRuleService;
use App\Application\UseCase\Owner\UpdatePricingRule\UpdatePricingRuleRequest;
use Exception;

class PricingRuleController
{
    private PricingRuleService $pricingRuleService;

    public function __construct(PricingRuleService $pricingRuleService)
    {
        $this->pricingRuleService = $pricingRuleService;
    }

    public function update(array $data): array
    {
        if (empty($data['parkingId']) || !isset($data['startDurationMinute']) || !isset($data['endDurationMinute']) || !isset($data['pricePerSlice']) || !isset($data['sliceInMinutes']) || empty($data['effectiveDate'])) {
            throw new Exception('Champs requis manquants');
        }
        $request = new UpdatePricingRuleRequest(
            (int)$data['parkingId'],
            (int)$data['startDurationMinute'],
            $data['endDurationMinute'] !== null ? (int)$data['endDurationMinute'] : null,
            (float)$data['pricePerSlice'],
            (int)$data['sliceInMinutes'],
            new \DateTimeImmutable($data['effectiveDate'])
        );
        $rule = $this->pricingRuleService->updatePricingRule($request);
        return [
            'id' => $rule->getPricingRuleId(),
            'parkingId' => $rule->getParkingId(),
            'startDurationMinute' => $rule->getStartDurationMinute(),
            'endDurationMinute' => $rule->getEndDurationMinute(),
            'pricePerSlice' => $rule->getPricePerSlice(),
            'sliceInMinutes' => $rule->getSliceInMinutes(),
            'effectiveDate' => $rule->getEffectiveDate()->format('Y-m-d'),
        ];
    }
}


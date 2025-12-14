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
        // Handle JSON body if $data is insufficient
        if (empty($data['parkingId'])) {
            $input = file_get_contents('php://input');
            $jsonData = json_decode($input, true);
            if (is_array($jsonData)) {
                $data = array_merge($data, $jsonData);
            }
        }

        if (empty($data['parkingId']) || !isset($data['startDurationMinute']) || !isset($data['pricePerSlice']) || !isset($data['sliceInMinutes']) || empty($data['effectiveDate'])) {
            $missing = [];
            if (empty($data['parkingId']))
                $missing[] = 'parkingId';
            if (!isset($data['startDurationMinute']))
                $missing[] = 'startDurationMinute';
            if (!isset($data['pricePerSlice']))
                $missing[] = 'pricePerSlice';
            if (!isset($data['sliceInMinutes']))
                $missing[] = 'sliceInMinutes';
            if (empty($data['effectiveDate']))
                $missing[] = 'effectiveDate';

            throw new \InvalidArgumentException('Champs requis manquants: ' . implode(', ', $missing));
        }

        $endDuration = isset($data['endDurationMinute']) && $data['endDurationMinute'] !== ''
            ? (int) $data['endDurationMinute']
            : null;

        $request = new UpdatePricingRuleRequest(
            (int) $data['parkingId'],
            (int) $data['startDurationMinute'],
            $endDuration,
            (float) $data['pricePerSlice'],
            (int) $data['sliceInMinutes'],
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

    public function list(array $data): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        $parkingId = $data['parkingId'] ?? $_GET['parkingId'] ?? null;

        if (!$parkingId) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing parkingId']);
            return;
        }

        $rules = $this->pricingRuleService->getPricingRulesByParkingId((int) $parkingId);

        $output = array_map(function ($rule) {
            return [
                'id' => $rule->getPricingRuleId(),
                'parkingId' => $rule->getParkingId(),
                'startDurationMinute' => $rule->getStartDurationMinute(),
                'endDurationMinute' => $rule->getEndDurationMinute(),
                'pricePerSlice' => $rule->getPricePerSlice(),
                'sliceInMinutes' => $rule->getSliceInMinutes(),
                'effectiveDate' => $rule->getEffectiveDate()->format('Y-m-d'),
            ];
        }, $rules);

        echo json_encode($output);
    }
}


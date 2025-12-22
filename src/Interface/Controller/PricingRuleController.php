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

    /**
     * Met à jour une règle de tarification
     */
    public function update(array $data): array
    {
        $data = $this->mergeJsonInput($data);
        $this->validatePricingRuleFields($data);

        $request = $this->buildUpdateRequest($data);
        $rule = $this->pricingRuleService->updatePricingRule($request);

        return $this->formatPricingRuleResponse($rule);
    }

    /**
     * Liste les règles de tarification d'un parking
     */
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
        $output = array_map([$this, 'formatPricingRuleResponse'], $rules);

        echo json_encode($output);
    }


    /**
     * Fusionne les données JSON avec les données existantes
     */
    private function mergeJsonInput(array $data): array
    {
        if (empty($data['parkingId'])) {
            $input = file_get_contents('php://input');
            $jsonData = json_decode($input, true);
            if (is_array($jsonData)) {
                $data = array_merge($data, $jsonData);
            }
        }
        return $data;
    }

    /**
     * Valide les champs requis pour une règle de tarification
     */
    private function validatePricingRuleFields(array $data): void
    {
        $requiredFields = [
            'parkingId' => empty($data['parkingId']),
            'startDurationMinute' => !isset($data['startDurationMinute']),
            'pricePerSlice' => !isset($data['pricePerSlice']),
            'sliceInMinutes' => !isset($data['sliceInMinutes']),
            'effectiveDate' => empty($data['effectiveDate']),
        ];

        $missing = array_keys(array_filter($requiredFields));

        if (!empty($missing)) {
            throw new \InvalidArgumentException('Champs requis manquants: ' . implode(', ', $missing));
        }
    }

    /**
     * Construit la requête de mise à jour
     */
    private function buildUpdateRequest(array $data): UpdatePricingRuleRequest
    {
        $endDuration = isset($data['endDurationMinute']) && $data['endDurationMinute'] !== ''
            ? (int) $data['endDurationMinute']
            : null;

        return new UpdatePricingRuleRequest(
            (int) $data['parkingId'],
            (int) $data['startDurationMinute'],
            $endDuration,
            (float) $data['pricePerSlice'],
            (int) $data['sliceInMinutes'],
            new \DateTimeImmutable($data['effectiveDate'])
        );
    }

    /**
     * Formate une règle de tarification en tableau
     */
    private function formatPricingRuleResponse($rule): array
    {
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

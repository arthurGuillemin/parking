<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\PricingRule;
use App\Domain\Repository\PricingRuleRepositoryInterface;
use PDO;

class PricingRuleRepository implements PricingRuleRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?PricingRule
    {
        // ... (stub)
        return null;
    }

    public function findByParkingId(int $parkingId): array
    {
        return [];
    }

    public function findApplicableRule(int $parkingId, \DateTimeImmutable $date): ?PricingRule
    {
        // Stub to return a default rule for tests/dev?
        // Implementing table query if needed.
        $stmt = $this->pdo->prepare(
            'SELECT * FROM pricing_rules WHERE parking_id = ? ORDER BY effective_date DESC LIMIT 1'
        );
        $stmt->execute([$parkingId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? $this->mapToEntity($data) : null;
    }

    public function save(PricingRule $rule): PricingRule
    {
        if ($rule->getPricingRuleId() === 0) {
            // Insert
            $stmt = $this->pdo->prepare(
                'INSERT INTO pricing_rules (parking_id, start_duration_minute, end_duration_minute, price_per_slice, slice_in_minutes, effective_date) VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $rule->getParkingId(),
                $rule->getStartDurationMinute(),
                $rule->getEndDurationMinute(),
                $rule->getPricePerSlice(),
                $rule->getSliceInMinutes(),
                $rule->getEffectiveDate()->format('Y-m-d H:i:s')
            ]);
            return new PricingRule(
                (int) $this->pdo->lastInsertId(),
                $rule->getParkingId(),
                $rule->getStartDurationMinute(),
                $rule->getEndDurationMinute(),
                $rule->getPricePerSlice(),
                $rule->getSliceInMinutes(),
                $rule->getEffectiveDate()
            );
        }
        return $rule;
    }

    private function mapToEntity(array $data): PricingRule
    {
        return new PricingRule(
            (int) $data['id'],
            (int) $data['parking_id'],
            (int) $data['start_duration_minute'],
            $data['end_duration_minute'] ? (int) $data['end_duration_minute'] : null,
            (float) $data['price_per_slice'],
            (int) $data['slice_in_minutes'],
            new \DateTimeImmutable($data['effective_date'])
        );
    }
}

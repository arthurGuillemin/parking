<?php

namespace App\Infrastructure\Persistence\File;

use App\Domain\Entity\PricingRule;
use App\Domain\Repository\PricingRuleRepositoryInterface;
use App\Infrastructure\Storage\FileStorage;
use DateTimeImmutable;

class FilePricingRuleRepository implements PricingRuleRepositoryInterface
{
    private FileStorage $storage;

    public function __construct()
    {
        $this->storage = new FileStorage(__DIR__ . '/storage/pricing-rules.json');
    }

    public function findById(int $id): ?PricingRule
    {
        foreach ($this->storage->read() as $row) {
            if ($row['id'] === $id) {
                return $this->mapToPricingRule($row);
            }
        }
        return null;
    }

    public function findByParkingId(int $parkingId): array
    {
        $results = [];

        foreach ($this->storage->read() as $row) {
            if ($row['parking_id'] === $parkingId) {
                $results[] = $this->mapToPricingRule($row);
            }
        }

        usort(
            $results,
            fn ($a, $b) => $b->getEffectiveDate() <=> $a->getEffectiveDate()
        );

        return $results;
    }

    public function findApplicableRule(int $parkingId, DateTimeImmutable $date): ?PricingRule
    {
        $rules = $this->findByParkingId($parkingId);

        foreach ($rules as $rule) {
            if ($rule->getEffectiveDate() <= $date) {
                return $rule;
            }
        }

        return null;
    }

    public function save(PricingRule $rule): PricingRule
    {
        $data = $this->storage->read();
        $found = false;

        foreach ($data as &$row) {
            if ($row['id'] === $rule->getPricingRuleId()) {
                $row = $this->mapFromPricingRule($rule);
                $found = true;
                break;
            }
        }

        if (!$found) {
            $data[] = $this->mapFromPricingRule($rule);
        }

        $this->storage->write($data);

        return $rule;
    }

    private function mapToPricingRule(array $row): PricingRule
    {
        return new PricingRule(
            id: (int) $row['id'],
            parkingId: (int) $row['parking_id'],
            startDurationMinute: (int) $row['start_duration_minute'],
            endDurationMinute: $row['end_duration_minute'] ?? null,
            pricePerSlice: (float) $row['price_per_slice'],
            sliceInMinutes: (int) $row['slice_in_minutes'],
            effectiveDate: new DateTimeImmutable($row['effective_date'])
        );
    }

    private function mapFromPricingRule(PricingRule $rule): array
    {
        return [
            'id' => $rule->getPricingRuleId(),
            'parking_id' => $rule->getParkingId(),
                        'start_duration_minute' => $rule->getStartDurationMinute(),
            'end_duration_minute' => $rule->getEndDurationMinute(),
            'price_per_slice' => $rule->getPricePerSlice(),
            'slice_in_minutes' => $rule->getSliceInMinutes(),
            'effective_date' => $rule->getEffectiveDate()->format('Y-m-d H:i:s'),
        ];
    }
}

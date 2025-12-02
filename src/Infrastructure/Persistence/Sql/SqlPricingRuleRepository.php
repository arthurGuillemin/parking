<?php

namespace App\Infrastructure\Persistence\Sql;

use App\Domain\Entity\PricingRule;
use App\Domain\Repository\PricingRuleRepositoryInterface;
use App\Infrastructure\Database\Database;
use PDO;
use PDOException;
use RuntimeException;
use DateTimeImmutable;

class SqlPricingRuleRepository implements PricingRuleRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    //trouver un tarif avec son id

    public function findById(int $id): ?PricingRule
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, parking_id, start_duration_minute, end_duration_minute, price_per_slice, slice_in_minutes, effective_date
                FROM pricing_rules
                WHERE id = :id
            ");
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch();
            if (!$row) return null;
            return $this->mapToPricingRule($row);

        } catch (PDOException $e) {
            throw new RuntimeException("aucun tariof trouvé avec cet id: " . $e->getMessage());
        }
    }

    //trouver un tarif avec l'id du parking

    public function findByParkingId(int $parkingId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, parking_id, start_duration_minute, end_duration_minute, price_per_slice, slice_in_minutes, effective_date
                FROM pricing_rules
                WHERE parking_id = :parking_id
                ORDER BY effective_date DESC
            ");
            $stmt->execute(['parking_id' => $parkingId]);

            $rows = $stmt->fetchAll();
            return array_map([$this, 'mapToPricingRule'], $rows);

        } catch (PDOException $e) {
            throw new RuntimeException("aucun tariof trouvé avec cet id de parking: " . $e->getMessage());
        }
    }
    //trouver un tarif avec la date effective
    public function findApplicableRule(int $parkingId, DateTimeImmutable $date): ?PricingRule
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, parking_id, start_duration_minute, end_duration_minute, price_per_slice, slice_in_minutes, effective_date
                FROM pricing_rules
                WHERE parking_id = :parking_id
                  AND effective_date <= :date
                ORDER BY effective_date DESC
                LIMIT 1
            ");
            $stmt->execute([
                'parking_id' => $parkingId,
                'date' => $date->format('Y-m-d H:i:s'),
            ]);

            $row = $stmt->fetch();
            if (!$row) return null;

            return $this->mapToPricingRule($row);

        } catch (PDOException $e) {
            throw new RuntimeException("aucun tariof trouvé avec cette date effective: " . $e->getMessage());
        }
    }

    //save un tarif

    public function save(PricingRule $rule): PricingRule
    {
        try {
            $existing = $this->findById($rule->getPricingRuleId());

            if ($existing) {
                $stmt = $this->db->prepare("
                    UPDATE pricing_rules
                    SET parking_id = :parking_id,
                        start_duration_minute = :start_duration_minute,
                        end_duration_minute = :end_duration_minute,
                        price_per_slice = :price_per_slice,
                        slice_in_minutes = :slice_in_minutes,
                        effective_date = :effective_date
                    WHERE id = :id
                ");
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO pricing_rules (id, parking_id, start_duration_minute, end_duration_minute, price_per_slice, slice_in_minutes, effective_date)
                    VALUES (:id, :parking_id, :start_duration_minute, :end_duration_minute, :price_per_slice, :slice_in_minutes, :effective_date)
                ");
            }

            $stmt->execute([
                'id' => $rule->getPricingRuleId(),
                'parking_id' => $rule->getParkingId(),
                'start_duration_minute' => $rule->getStartDurationMinute(),
                'end_duration_minute' => $rule->getEndDurationMinute(),
                'price_per_slice' => $rule->getPricePerSlice(),
                'slice_in_minutes' => $rule->getSliceInMinutes(),
                'effective_date' => $rule->getEffectiveDate()->format('Y-m-d H:i:s'),
            ]);

            return $rule;

        } catch (PDOException $e) {
            throw new RuntimeException("erreur dans le save du tarif: " . $e->getMessage());
        }
    }

    private function mapToPricingRule(array $row): PricingRule
    {
        return new PricingRule(
            id: (int)$row['id'],
            parkingId: (int)$row['parking_id'],
            startDurationMinute: (int)$row['start_duration_minute'],
            endDurationMinute: $row['end_duration_minute'] !== null ? (int)$row['end_duration_minute'] : null,
            pricePerSlice: (float)$row['price_per_slice'],
            sliceInMinutes: (int)$row['slice_in_minutes'],
            effectiveDate: new DateTimeImmutable($row['effective_date'])
        );
    }
}

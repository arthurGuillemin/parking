<?php

namespace App\Domain\Repository;

use App\Domain\Entity\PricingRule;

interface PricingRuleRepository
{
    public function findById(int $id): ?PricingRule;

    /**
     * Retourne toutes les règles tarifaires d'un parking.
     */
    public function findByParkingId(int $parkingId): array;

    /**
     * Retourne la règle applicable à une date donnée.
     */
    public function findApplicableRule(
        int $parkingId,
        \DateTimeImmutable $date
    ): ?PricingRule;

    public function save(PricingRule $rule): PricingRule;
}

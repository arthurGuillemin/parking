<?php

namespace Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use App\Domain\Service\PricingService;
use App\Domain\Repository\PricingRuleRepositoryInterface;
use App\Domain\Entity\PricingRule;
use DateInterval;
use DateTimeImmutable;

class PricingServiceTest extends TestCase
{
    public function testCalculatePriceStandard()
    {
        $repo = $this->createMock(PricingRuleRepositoryInterface::class);
        $date = new DateTimeImmutable('2024-06-01 10:00:00');

        // Rule: 0-60min, 2€ per 15min slice
        $rule1 = new PricingRule(1, 1, 0, 60, 2.0, 15, new DateTimeImmutable('2024-01-01'));
        // Rule: 60-INF, 1€ per 15min slice
        $rule2 = new PricingRule(2, 1, 60, null, 1.0, 15, new DateTimeImmutable('2024-01-01'));

        $repo->method('findByParkingId')->willReturn([$rule1, $rule2]);

        $service = new PricingService($repo);

        // Test 1: 45 minutes (within first rule)
        // 45 / 15 = 3 slices. 3 * 2€ = 6€
        $duration1 = new DateInterval('PT45M');
        $this->assertEquals(6.0, $service->calculatePrice(1, $duration1, $date));

        // Test 2: 90 minutes (1h in rule 1, 30m in rule 2)
        // Rule 1: 60/15 = 4 slices * 2€ = 8€
        // Rule 2: 30/15 = 2 slices * 1€ = 2€
        // Total: 10€
        $duration2 = new DateInterval('PT1H30M');
        $this->assertEquals(10.0, $service->calculatePrice(1, $duration2, $date));
    }

    public function testCalculatePriceWithDateFiltering()
    {
        $repo = $this->createMock(PricingRuleRepositoryInterface::class);

        // Old Rule (effective Jan 1): 2€
        $ruleOld = new PricingRule(1, 1, 0, 60, 2.0, 15, new DateTimeImmutable('2024-01-01'));
        // New Rule (effective Jun 1): 3€
        $ruleNew = new PricingRule(2, 1, 0, 60, 3.0, 15, new DateTimeImmutable('2024-06-01'));

        $repo->method('findByParkingId')->willReturn([$ruleOld, $ruleNew]);
        $service = new PricingService($repo);

        $duration = new DateInterval('PT30M'); // 2 slices

        // At May 1st: Should use Old Rule (2€ * 2 = 4€)
        $dateMay = new DateTimeImmutable('2024-05-01');
        $this->assertEquals(4.0, $service->calculatePrice(1, $duration, $dateMay));

        // At July 1st: Should use New Rule (3€ * 2 = 6€)
        $dateJuly = new DateTimeImmutable('2024-07-01');
        $this->assertEquals(6.0, $service->calculatePrice(1, $duration, $dateJuly));
    }
}

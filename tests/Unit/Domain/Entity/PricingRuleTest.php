<?php

namespace Unit\Domain\Entity;

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\PricingRule;

class PricingRuleTest extends TestCase
{
    public function testGetters()
    {
        $rule = new PricingRule(1, 2, 0, 60, 2.5, 15, new \DateTimeImmutable('2025-11-28'));
        $this->assertEquals(1, $rule->getPricingRuleId());
        $this->assertEquals(2, $rule->getParkingId());
        $this->assertEquals(0, $rule->getStartDurationMinute());
        $this->assertEquals(60, $rule->getEndDurationMinute());
        $this->assertEquals(2.5, $rule->getPricePerSlice());
        $this->assertEquals(15, $rule->getSliceInMinutes());
        $this->assertEquals(new \DateTimeImmutable('2025-11-28'), $rule->getEffectiveDate());
    }
}

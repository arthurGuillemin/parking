<?php

namespace Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\PricingRule;
use DateTimeImmutable;

class PricingRuleTest extends TestCase
{
    public function testGetters()
    {
        $id = 1;
        $parkingId = 10;
        $start = 0;
        $end = 60;
        $price = 2.5;
        $slice = 15;
        $date = new DateTimeImmutable('2024-01-01');

        $rule = new PricingRule($id, $parkingId, $start, $end, $price, $slice, $date);

        $this->assertEquals($id, $rule->getPricingRuleId());
        $this->assertEquals($parkingId, $rule->getParkingId());
        $this->assertEquals($start, $rule->getStartDurationMinute());
        $this->assertEquals($end, $rule->getEndDurationMinute());
        $this->assertEquals($price, $rule->getPricePerSlice());
        $this->assertEquals($slice, $rule->getSliceInMinutes());
        $this->assertEquals($date, $rule->getEffectiveDate());
    }

    public function testNullableEndDuration()
    {
        $rule = new PricingRule(1, 1, 60, null, 1.0, 15, new DateTimeImmutable());
        $this->assertNull($rule->getEndDurationMinute());
    }
}

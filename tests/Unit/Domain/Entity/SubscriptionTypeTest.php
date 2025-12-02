<?php

namespace Unit\Domain\Entity;

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\SubscriptionType;

class SubscriptionTypeTest extends TestCase
{
    public function testGetters()
    {
        $type = new SubscriptionType(1, 'Premium', 'Full access');
        $this->assertEquals(1, $type->getSubscriptionTypeId());
        $this->assertEquals('Premium', $type->getName());
        $this->assertEquals('Full access', $type->getDescription());
    }
}


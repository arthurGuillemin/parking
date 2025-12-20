<?php

namespace Unit\Domain\Entity;

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\SubscriptionSlot;

class SubscriptionSlotTest extends TestCase
{
    public function testGetters()
    {
        $slot = new SubscriptionSlot(1, 2, 1, new \DateTimeImmutable('08:00'), new \DateTimeImmutable('18:00'));
        $this->assertEquals(1, $slot->getSubscriptionSlotId());
        $this->assertEquals(2, $slot->getSubscriptionTypeId());
        $this->assertEquals(1, $slot->getWeekday());
        $this->assertEquals(new \DateTimeImmutable('08:00'), $slot->getStartTime());
        $this->assertEquals(new \DateTimeImmutable('18:00'), $slot->getEndTime());
    }
}

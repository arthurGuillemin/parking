<?php

namespace Tests\Unit\Domain\Entity;

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\SubscriptionSlot;

class SubscriptionSlotTest extends TestCase
{
    private SubscriptionSlot $slot;
    private int $subscriptionTypeId = 1;
    private int $weekday = 3; // Wednesday
    private \DateTimeImmutable $startTime;
    private \DateTimeImmutable $endTime;

    protected function setUp(): void
    {
        $this->startTime = new \DateTimeImmutable('18:00:00');
        $this->endTime = new \DateTimeImmutable('22:00:00');

        $this->slot = new SubscriptionSlot(
            1,
            $this->subscriptionTypeId,
            $this->weekday,
            $this->startTime,
            $this->endTime
        );
    }

    public function testGetSubscriptionSlotId(): void
    {
        $this->assertEquals(1, $this->slot->getSubscriptionSlotId());
    }

    public function testGetSubscriptionTypeId(): void
    {
        $this->assertEquals($this->subscriptionTypeId, $this->slot->getSubscriptionTypeId());
    }

    public function testGetWeekday(): void
    {
        $this->assertEquals($this->weekday, $this->slot->getWeekday());
    }

    public function testGetStartTime(): void
    {
        $this->assertEquals($this->startTime, $this->slot->getStartTime());
    }

    public function testGetEndTime(): void
    {
        $this->assertEquals($this->endTime, $this->slot->getEndTime());
    }

    public function testWeekdayRange(): void
    {
        // Test all valid weekdays
        for ($weekday = 1; $weekday <= 7; $weekday++) {
            $slot = new SubscriptionSlot(
                1,
                $this->subscriptionTypeId,
                $weekday,
                $this->startTime,
                $this->endTime
            );
            $this->assertEquals($weekday, $slot->getWeekday());
        }
    }
}
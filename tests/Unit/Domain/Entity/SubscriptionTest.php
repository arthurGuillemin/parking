<?php
namespace Unit\Domain\Entity;

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\Subscription;

class SubscriptionTest extends TestCase
{
    public function testGetters()
    {
        $start = new \DateTimeImmutable('-1 month');
        $end = new \DateTimeImmutable('+1 month');
        $subscription = new Subscription(1, 'user-uuid', 2, 3, $start, $end, 'active', 99.99);
        $this->assertEquals(1, $subscription->getSubscriptionId());
        $this->assertEquals('user-uuid', $subscription->getUserId());
        $this->assertEquals(2, $subscription->getParkingId());
        $this->assertEquals(3, $subscription->getTypeId());
        $this->assertEquals($start, $subscription->getStartDate());
        $this->assertEquals($end, $subscription->getEndDate());
        $this->assertEquals('active', $subscription->getStatus());
        $this->assertEquals(99.99, $subscription->getMonthlyPrice());
    }

    public function testIsActiveAtWithActiveStatusAndNoEndDate()
    {
        $start = new \DateTimeImmutable('-1 month');
        $subscription = new Subscription(1, 'user-uuid', 2, 3, $start, null, 'active', 99.99);
        $now = new \DateTimeImmutable();
        $this->assertTrue($subscription->isActiveAt($now));
    }

    public function testIsActiveAtWithInactiveStatus()
    {
        $start = new \DateTimeImmutable('-1 month');
        $end = new \DateTimeImmutable('+1 month');
        $subscription = new Subscription(1, 'user-uuid', 2, 3, $start, $end, 'cancelled', 99.99);
        $now = new \DateTimeImmutable();
        $this->assertFalse($subscription->isActiveAt($now));
    }

    public function testIsActiveAtWithNotStartedYet()
    {
        $start = new \DateTimeImmutable('+1 day');
        $end = new \DateTimeImmutable('+2 months');
        $subscription = new Subscription(1, 'user-uuid', 2, 3, $start, $end, 'active', 99.99);
        $now = new \DateTimeImmutable();
        $this->assertFalse($subscription->isActiveAt($now));
    }

    public function testIsActiveAtWithExpired()
    {
        $start = new \DateTimeImmutable('-2 months');
        $end = new \DateTimeImmutable('-1 month');
        $subscription = new Subscription(1, 'user-uuid', 2, 3, $start, $end, 'active', 99.99);
        $now = new \DateTimeImmutable();
        $this->assertFalse($subscription->isActiveAt($now));
    }

    public function testIsActiveAtWithEndDateInFuture()
    {
        $start = new \DateTimeImmutable('-1 month');
        $end = new \DateTimeImmutable('+1 month');
        $subscription = new Subscription(1, 'user-uuid', 2, 3, $start, $end, 'active', 99.99);
        $now = new \DateTimeImmutable();
        $this->assertTrue($subscription->isActiveAt($now));
    }
}


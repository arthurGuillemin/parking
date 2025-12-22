<?php

namespace Tests\Unit\Domain\Entity;

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\Subscription;

class SubscriptionTest extends TestCase
{
    private Subscription $subscription;
    private string $userId = 'user-123';
    private int $parkingId = 1;
    private ?int $typeId = 2;
    private \DateTimeImmutable $startDate;
    private \DateTimeImmutable $endDate;
    private string $status = 'active';
    private float $monthlyPrice = 49.99;

    protected function setUp(): void
    {
        $this->startDate = new \DateTimeImmutable('2025-01-01');
        $this->endDate = new \DateTimeImmutable('2025-02-01');

        $this->subscription = new Subscription(
            1,
            $this->userId,
            $this->parkingId,
            $this->typeId,
            $this->startDate,
            $this->endDate,
            $this->status,
            $this->monthlyPrice
        );
    }

    // ===== GETTERS TESTS =====

    public function testGetSubscriptionId(): void
    {
        $this->assertEquals(1, $this->subscription->getSubscriptionId());
    }

    public function testGetUserId(): void
    {
        $this->assertEquals($this->userId, $this->subscription->getUserId());
    }

    public function testGetParkingId(): void
    {
        $this->assertEquals($this->parkingId, $this->subscription->getParkingId());
    }

    public function testGetTypeId(): void
    {
        $this->assertEquals($this->typeId, $this->subscription->getTypeId());
    }

    public function testGetTypeIdCanBeNull(): void
    {
        $subscription = new Subscription(
            1,
            $this->userId,
            $this->parkingId,
            null, // 24/7 subscription
            $this->startDate,
            $this->endDate,
            $this->status,
            $this->monthlyPrice
        );

        $this->assertNull($subscription->getTypeId());
    }

    public function testGetStartDate(): void
    {
        $this->assertEquals($this->startDate, $this->subscription->getStartDate());
    }

    public function testGetEndDate(): void
    {
        $this->assertEquals($this->endDate, $this->subscription->getEndDate());
    }

    public function testGetEndDateCanBeNull(): void
    {
        $subscription = new Subscription(
            1,
            $this->userId,
            $this->parkingId,
            $this->typeId,
            $this->startDate,
            null,
            $this->status,
            $this->monthlyPrice
        );

        $this->assertNull($subscription->getEndDate());
    }

    public function testGetStatus(): void
    {
        $this->assertEquals($this->status, $this->subscription->getStatus());
    }

    public function testGetMonthlyPrice(): void
    {
        $this->assertEquals($this->monthlyPrice, $this->subscription->getMonthlyPrice());
    }

    // ===== isActiveAt() TESTS =====

    public function testIsActiveAtWithActiveStatusAndNoEndDate(): void
    {
        $start = new \DateTimeImmutable('-1 month');
        $subscription = new Subscription(1, 'user-uuid', 2, 3, $start, null, 'active', 99.99);
        $now = new \DateTimeImmutable();
        $this->assertTrue($subscription->isActiveAt($now));
    }

    public function testIsActiveAtWithInactiveStatus(): void
    {
        $start = new \DateTimeImmutable('-1 month');
        $end = new \DateTimeImmutable('+1 month');
        $subscription = new Subscription(1, 'user-uuid', 2, 3, $start, $end, 'cancelled', 99.99);
        $now = new \DateTimeImmutable();
        $this->assertFalse($subscription->isActiveAt($now));
    }

    public function testIsActiveAtWithNotStartedYet(): void
    {
        $start = new \DateTimeImmutable('+1 day');
        $end = new \DateTimeImmutable('+2 months');
        $subscription = new Subscription(1, 'user-uuid', 2, 3, $start, $end, 'active', 99.99);
        $now = new \DateTimeImmutable();
        $this->assertFalse($subscription->isActiveAt($now));
    }

    public function testIsActiveAtWithExpired(): void
    {
        $start = new \DateTimeImmutable('-2 months');
        $end = new \DateTimeImmutable('-1 month');
        $subscription = new Subscription(1, 'user-uuid', 2, 3, $start, $end, 'active', 99.99);
        $now = new \DateTimeImmutable();
        $this->assertFalse($subscription->isActiveAt($now));
    }

    public function testIsActiveAtWithEndDateInFuture(): void
    {
        $start = new \DateTimeImmutable('-1 month');
        $end = new \DateTimeImmutable('+1 month');
        $subscription = new Subscription(1, 'user-uuid', 2, 3, $start, $end, 'active', 99.99);
        $now = new \DateTimeImmutable();
        $this->assertTrue($subscription->isActiveAt($now));
    }
}

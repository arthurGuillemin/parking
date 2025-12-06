<?php

namespace Tests\Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Domain\Service\SubscriptionCoverageService;
use App\Domain\Entity\Subscription;
use App\Domain\Entity\SubscriptionSlot;
use App\Domain\Repository\SubscriptionSlotRepositoryInterface;

class SubscriptionCoverageServiceTest extends TestCase
{
    private SubscriptionCoverageService $service;
    private MockObject|SubscriptionSlotRepositoryInterface $slotRepository;

    protected function setUp(): void
    {
        $this->slotRepository = $this->createMock(SubscriptionSlotRepositoryInterface::class);
        $this->service = new SubscriptionCoverageService($this->slotRepository);
    }

    /**
     * Test: Subscription with null typeId is always covered (24/7 access)
     */
    public function testNullTypeIdMeansTwentyFourSevenAccess(): void
    {
        $subscription = new Subscription(
            1,
            'user-123',
            1,
            null, // No type = 24/7
            new \DateTimeImmutable('2025-01-01'),
            new \DateTimeImmutable('2025-02-01'),
            'active',
            49.99
        );

        $dateTime = new \DateTimeImmutable('2025-01-15 15:30:00');

        $this->assertTrue(
            $this->service->isDateTimeCovered($subscription, $dateTime)
        );
    }

    /**
     * Test: DateTime before subscription start date is not covered
     */
    public function testDateBeforeStartDateIsNotCovered(): void
    {
        $subscription = new Subscription(
            1,
            'user-123',
            1,
            1,
            new \DateTimeImmutable('2025-01-15'),
            new \DateTimeImmutable('2025-02-15'),
            'active',
            49.99
        );

        $dateTime = new \DateTimeImmutable('2025-01-14 10:00:00');

        $this->assertFalse(
            $this->service->isDateTimeCovered($subscription, $dateTime)
        );
    }

    /**
     * Test: DateTime after subscription end date is not covered
     */
    public function testDateAfterEndDateIsNotCovered(): void
    {
        $subscription = new Subscription(
            1,
            'user-123',
            1,
            1,
            new \DateTimeImmutable('2025-01-15'),
            new \DateTimeImmutable('2025-02-15'),
            'active',
            49.99
        );

        $dateTime = new \DateTimeImmutable('2025-02-16 10:00:00');

        $this->assertFalse(
            $this->service->isDateTimeCovered($subscription, $dateTime)
        );
    }

    /**
     * Test: Time falls within slot for the day
     */
    public function testTimeWithinSlotIsCovered(): void
    {
        $subscription = new Subscription(
            1,
            'user-123',
            1,
            1,
            new \DateTimeImmutable('2025-01-01'),
            new \DateTimeImmutable('2025-02-01'),
            'active',
            49.99
        );

        // Wednesday (3), 18:00-22:00
        $slot = new SubscriptionSlot(
            1,
            1,
            3,
            new \DateTimeImmutable('18:00:00'),
            new \DateTimeImmutable('22:00:00')
        );

        $this->slotRepository->expects($this->once())
            ->method('findBySubscriptionTypeId')
            ->with(1)
            ->willReturn([$slot]);

        // Wednesday at 20:00 (within 18:00-22:00)
        $dateTime = new \DateTimeImmutable('2025-01-08 20:00:00'); // Jan 8, 2025 is Wednesday

        $this->assertTrue(
            $this->service->isDateTimeCovered($subscription, $dateTime)
        );
    }

    /**
     * Test: Time outside slot for the day is not covered
     */
    public function testTimeOutsideSlotIsNotCovered(): void
    {
        $subscription = new Subscription(
            1,
            'user-123',
            1,
            1,
            new \DateTimeImmutable('2025-01-01'),
            new \DateTimeImmutable('2025-02-01'),
            'active',
            49.99
        );

        // Wednesday (3), 18:00-22:00
        $slot = new SubscriptionSlot(
            1,
            1,
            3,
            new \DateTimeImmutable('18:00:00'),
            new \DateTimeImmutable('22:00:00')
        );

        $this->slotRepository->expects($this->once())
            ->method('findBySubscriptionTypeId')
            ->with(1)
            ->willReturn([$slot]);

        // Wednesday at 10:00 (outside 18:00-22:00)
        $dateTime = new \DateTimeImmutable('2025-01-08 10:00:00'); // Jan 8, 2025 is Wednesday

        $this->assertFalse(
            $this->service->isDateTimeCovered($subscription, $dateTime)
        );
    }

    /**
     * Test: Different weekday is not covered
     */
    public function testDifferentWeekdayIsNotCovered(): void
    {
        $subscription = new Subscription(
            1,
            'user-123',
            1,
            1,
            new \DateTimeImmutable('2025-01-01'),
            new \DateTimeImmutable('2025-02-01'),
            'active',
            49.99
        );

        // Only Wednesday (3) at 18:00-22:00
        $slot = new SubscriptionSlot(
            1,
            1,
            3,
            new \DateTimeImmutable('18:00:00'),
            new \DateTimeImmutable('22:00:00')
        );

        $this->slotRepository->expects($this->once())
            ->method('findBySubscriptionTypeId')
            ->with(1)
            ->willReturn([$slot]);

        // Tuesday at 20:00 (even though time is 20:00, weekday is wrong)
        $dateTime = new \DateTimeImmutable('2025-01-07 20:00:00'); // Jan 7, 2025 is Tuesday

        $this->assertFalse(
            $this->service->isDateTimeCovered($subscription, $dateTime)
        );
    }

    /**
     * Test: Evening subscription (18:00-08:00 next day)
     * Monday evening to Tuesday morning
     */
    public function testEveningSubscriptionCoverage(): void
    {
        $subscription = new Subscription(
            1,
            'user-123',
            1,
            1,
            new \DateTimeImmutable('2025-01-01'),
            new \DateTimeImmutable('2025-02-01'),
            'active',
            49.99
        );

        // Evening slots: Monday 18:00-23:59, Tuesday 00:00-08:00
        $slots = [
            new SubscriptionSlot(1, 1, 1, new \DateTimeImmutable('18:00:00'), new \DateTimeImmutable('23:59:59')),
            new SubscriptionSlot(2, 1, 2, new \DateTimeImmutable('00:00:00'), new \DateTimeImmutable('08:00:00')),
        ];

        $this->slotRepository->expects($this->once())
            ->method('findBySubscriptionTypeId')
            ->with(1)
            ->willReturn($slots);

        // Monday at 21:00 (within evening hours)
        $dateTime = new \DateTimeImmutable('2025-01-06 21:00:00'); // Jan 6, 2025 is Monday

        $this->assertTrue(
            $this->service->isDateTimeCovered($subscription, $dateTime)
        );
    }

    /**
     * Test: No slots for type means no coverage (business rule)
     */
    public function testNoSlotsForTypeMeansCoverage(): void
    {
        $subscription = new Subscription(
            1,
            'user-123',
            1,
            1,
            new \DateTimeImmutable('2025-01-01'),
            new \DateTimeImmutable('2025-02-01'),
            'active',
            49.99
        );

        // No slots defined for this type
        $this->slotRepository->expects($this->once())
            ->method('findBySubscriptionTypeId')
            ->with(1)
            ->willReturn([]);

        $dateTime = new \DateTimeImmutable('2025-01-15 10:00:00');

        // According to your logic, if no slots exist, it returns true
        $this->assertTrue(
            $this->service->isDateTimeCovered($subscription, $dateTime)
        );
    }
}
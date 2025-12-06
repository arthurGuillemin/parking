<?php

namespace Tests\Unit\Application\UseCase;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Application\UseCase\User\AddSubscription\AddSubscriptionUseCase;
use App\Application\UseCase\User\AddSubscription\AddSubscriptionRequest;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Domain\Entity\Subscription;

class AddSubscriptionUseCaseTest extends TestCase
{
    private AddSubscriptionUseCase $useCase;
    private MockObject|SubscriptionRepositoryInterface $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(SubscriptionRepositoryInterface::class);
        $this->useCase = new AddSubscriptionUseCase($this->repository);
    }

    /**
     * Test: Valid subscription is created successfully
     */
    public function testValidSubscriptionIsCreated(): void
    {
        $startDate = new \DateTimeImmutable('2025-01-01');
        $endDate = new \DateTimeImmutable('2025-02-01');

        $request = new AddSubscriptionRequest(
            'user-123',
            1,
            2,
            $startDate,
            $endDate,
            49.99
        );

        $savedSubscription = new Subscription(
            1,
            'user-123',
            1,
            2,
            $startDate,
            $endDate,
            'active',
            49.99
        );

        $this->repository->expects($this->once())
            ->method('save')
            ->willReturn($savedSubscription);

        $result = $this->useCase->execute($request);

        $this->assertEquals(1, $result->id);
        $this->assertEquals('user-123', $result->userId);
        $this->assertEquals('active', $result->status);
    }

    /**
     * Test: Subscription duration must be at least 1 month
     */
    public function testSubscriptionDurationMustBeAtLeastOneMonth(): void
    {
        $startDate = new \DateTimeImmutable('2025-01-01');
        $endDate = new \DateTimeImmutable('2025-01-15'); // Only 14 days

        $request = new AddSubscriptionRequest(
            'user-123',
            1,
            2,
            $startDate,
            $endDate,
            49.99
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Subscription duration must be at least 1 month.');

        $this->useCase->execute($request);
    }

    /**
     * Test: Subscription duration cannot exceed 1 year
     */
    public function testSubscriptionDurationCannotExceedOneYear(): void
    {
        $startDate = new \DateTimeImmutable('2025-01-01');
        $endDate = new \DateTimeImmutable('2026-01-15'); // 1 year + 14 days

        $request = new AddSubscriptionRequest(
            'user-123',
            1,
            2,
            $startDate,
            $endDate,
            49.99
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Subscription duration cannot exceed 1 year.');

        $this->useCase->execute($request);
    }

    /**
     * Test: If no end date provided, defaults to 1 year
     */
    public function testNullEndDateDefaultsToOneYear(): void
    {
        $startDate = new \DateTimeImmutable('2025-01-01');

        $request = new AddSubscriptionRequest(
            'user-123',
            1,
            2,
            $startDate,
            null, // No end date
            49.99
        );

        $savedSubscription = new Subscription(
            1,
            'user-123',
            1,
            2,
            $startDate,
            $startDate->add(new \DateInterval('P1Y')),
            'active',
            49.99
        );

        $this->repository->expects($this->once())
            ->method('save')
            ->willReturn($savedSubscription);

        $result = $this->useCase->execute($request);

        // End date should be 1 year after start
        $expectedEnd = $startDate->add(new \DateInterval('P1Y'))->format('Y-m-d H:i:s');
        $this->assertEquals($expectedEnd, $result->endDate);
    }

    /**
     * Test: 24/7 subscription (typeId = null) is allowed
     */
    public function testNullTypeIdIsAllowed(): void
    {
        $startDate = new \DateTimeImmutable('2025-01-01');
        $endDate = new \DateTimeImmutable('2025-02-01');

        $request = new AddSubscriptionRequest(
            'user-123',
            1,
            null, // 24/7 access
            $startDate,
            $endDate,
            99.99
        );

        $savedSubscription = new Subscription(
            1,
            'user-123',
            1,
            null,
            $startDate,
            $endDate,
            'active',
            99.99
        );

        $this->repository->expects($this->once())
            ->method('save')
            ->willReturn($savedSubscription);

        $result = $this->useCase->execute($request);

        $this->assertNull($result->typeId);
    }
}
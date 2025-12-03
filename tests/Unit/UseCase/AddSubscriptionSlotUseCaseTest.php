<?php

namespace Tests\Unit\Application\UseCase;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Application\UseCase\Owner\AddSubscriptionSlot\AddSubscriptionSlotUseCase;
use App\Application\UseCase\Owner\AddSubscriptionSlot\AddSubscriptionSlotRequest;
use App\Domain\Repository\SubscriptionSlotRepositoryInterface;
use App\Domain\Entity\SubscriptionSlot;

class AddSubscriptionSlotUseCaseTest extends TestCase
{
    private AddSubscriptionSlotUseCase $useCase;
    private MockObject|SubscriptionSlotRepositoryInterface $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(SubscriptionSlotRepositoryInterface::class);
        $this->useCase = new AddSubscriptionSlotUseCase($this->repository);
    }

    /**
     * Test: Valid slot is created successfully
     */
    public function testValidSlotIsCreated(): void
    {
        $startTime = new \DateTimeImmutable('18:00:00');
        $endTime = new \DateTimeImmutable('22:00:00');

        $request = new AddSubscriptionSlotRequest(
            1,
            3, // Wednesday
            $startTime,
            $endTime
        );

        $savedSlot = new SubscriptionSlot(
            1,
            1,
            3,
            $startTime,
            $endTime
        );

        $this->repository->expects($this->once())
            ->method('save')
            ->willReturn($savedSlot);

        $result = $this->useCase->execute($request);

        $this->assertEquals(1, $result->getSubscriptionSlotId());
        $this->assertEquals(3, $result->getWeekday());
    }

    /**
     * Test: Weekday must be between 1 and 7
     */
    public function testWeekdayMustBeBetweenOneAndSeven(): void
    {
        $request = new AddSubscriptionSlotRequest(
            1,
            8, // Invalid weekday
            new \DateTimeImmutable('18:00:00'),
            new \DateTimeImmutable('22:00:00')
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Weekday must be between 1 (Monday) and 7 (Sunday).');

        $this->useCase->execute($request);
    }

    /**
     * Test: Start time must be before end time
     */
    public function testStartTimeMustBeBeforeEndTime(): void
    {
        $request = new AddSubscriptionSlotRequest(
            1,
            3,
            new \DateTimeImmutable('22:00:00'),
            new \DateTimeImmutable('18:00:00') // End before start
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Start time must be before end time.');

        $this->useCase->execute($request);
    }

    /**
     * Test: All valid weekdays are accepted
     */
    public function testAllValidWeekdaysAreAccepted(): void
    {
        $startTime = new \DateTimeImmutable('18:00:00');
        $endTime = new \DateTimeImmutable('22:00:00');

        for ($weekday = 1; $weekday <= 7; $weekday++) {
            // Créer un nouveau mock à chaque itération
            $repository = $this->createMock(SubscriptionSlotRepositoryInterface::class);
            $useCase = new AddSubscriptionSlotUseCase($repository);

            $request = new AddSubscriptionSlotRequest(1, $weekday, $startTime, $endTime);

            $savedSlot = new SubscriptionSlot(1,1,$weekday,$startTime,$endTime);

            $repository->expects($this->once())
                ->method('save')
                ->willReturn($savedSlot);

            $result = $useCase->execute($request);

            $this->assertEquals($weekday, $result->getWeekday());
        }
    }
}

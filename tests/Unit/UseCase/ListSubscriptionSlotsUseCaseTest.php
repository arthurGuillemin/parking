<?php

namespace Tests\Unit\UseCase;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Application\UseCase\Owner\ListSubscriptionSlots\ListSubscriptionSlotsUseCase;
use App\Application\UseCase\Owner\ListSubscriptionSlots\ListSubscriptionSlotsRequest;
use App\Application\UseCase\Owner\AddSubscriptionSlot\AddSubscriptionSlotResponse;
use App\Domain\Repository\SubscriptionSlotRepositoryInterface;
use App\Domain\Entity\SubscriptionSlot;

class ListSubscriptionSlotsUseCaseTest extends TestCase
{
    private ListSubscriptionSlotsUseCase $useCase;
    private MockObject|SubscriptionSlotRepositoryInterface $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(SubscriptionSlotRepositoryInterface::class);
        $this->useCase = new ListSubscriptionSlotsUseCase($this->repository);
    }

    public function testListSlotsSuccess(): void
    {
        $slot1 = new SubscriptionSlot(
            1,
            1,
            1,
            new \DateTimeImmutable('10:00:00'),
            new \DateTimeImmutable('12:00:00')
        );

        $this->repository->expects($this->once())
            ->method('findBySubscriptionTypeId')
            ->with(1)
            ->willReturn([$slot1]);

        $request = new ListSubscriptionSlotsRequest(1);
        $response = $this->useCase->execute($request);

        $this->assertCount(1, $response);
        $this->assertInstanceOf(AddSubscriptionSlotResponse::class, $response[0]);
        $this->assertEquals(1, $response[0]->id);
    }
}

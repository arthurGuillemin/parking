<?php

namespace Tests\Unit\UseCase;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Application\UseCase\Owner\DeleteSubscriptionSlot\DeleteSubscriptionSlotUseCase;
use App\Application\UseCase\Owner\DeleteSubscriptionSlot\DeleteSubscriptionSlotRequest;
use App\Domain\Repository\SubscriptionSlotRepositoryInterface;
use App\Domain\Entity\SubscriptionSlot;

class DeleteSubscriptionSlotUseCaseTest extends TestCase
{
    private DeleteSubscriptionSlotUseCase $useCase;
    private MockObject|SubscriptionSlotRepositoryInterface $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(SubscriptionSlotRepositoryInterface::class);
        $this->useCase = new DeleteSubscriptionSlotUseCase($this->repository);
    }

    public function testDeleteSubscriptionSlotSuccess(): void
    {
        $slot = new SubscriptionSlot(
            1,
            1,
            1,
            new \DateTimeImmutable('10:00:00'),
            new \DateTimeImmutable('12:00:00')
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($slot);

        $this->repository->expects($this->once())
            ->method('delete')
            ->with(1);

        $request = new DeleteSubscriptionSlotRequest(1);
        $this->useCase->execute($request);
    }

    public function testDeleteSubscriptionSlotNotFound(): void
    {
        $this->repository->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Subscription slot not found.');

        $request = new DeleteSubscriptionSlotRequest(999);
        $this->useCase->execute($request);
    }
}

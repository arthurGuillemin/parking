<?php

namespace Tests\Unit\UseCase;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Application\UseCase\User\CancelSubscription\CancelSubscriptionUseCase;
use App\Application\UseCase\User\CancelSubscription\CancelSubscriptionRequest;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Domain\Entity\Subscription;
use App\Application\DTO\Response\SubscriptionResponse;

class CancelSubscriptionUseCaseTest extends TestCase
{
    private CancelSubscriptionUseCase $useCase;
    private MockObject|SubscriptionRepositoryInterface $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(SubscriptionRepositoryInterface::class);
        $this->useCase = new CancelSubscriptionUseCase($this->repository);
    }

    public function testCancelSubscriptionSuccess(): void
    {
        $subscription = new Subscription(
            1,
            'user-123',
            1,
            null,
            new \DateTimeImmutable('2025-01-01'),
            new \DateTimeImmutable('2026-01-01'),
            'active',
            50.0
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($subscription);

        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Subscription $sub) {
                return $sub->getStatus() === 'cancelled';
            }));

        $request = new CancelSubscriptionRequest(1);
        $response = $this->useCase->execute($request);

        $this->assertInstanceOf(SubscriptionResponse::class, $response);
        $this->assertEquals('cancelled', $response->status);
    }

    public function testCancelSubscriptionNotFound(): void
    {
        $this->repository->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Subscription not found.');

        $request = new CancelSubscriptionRequest(999);
        $this->useCase->execute($request);
    }
}

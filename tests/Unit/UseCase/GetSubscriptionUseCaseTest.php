<?php

namespace Tests\Unit\UseCase;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Application\UseCase\User\GetSubscription\GetSubscriptionUseCase;
use App\Application\UseCase\User\GetSubscription\GetSubscriptionRequest;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Domain\Entity\Subscription;
use App\Application\DTO\Response\SubscriptionResponse;

class GetSubscriptionUseCaseTest extends TestCase
{
    private GetSubscriptionUseCase $useCase;
    private MockObject|SubscriptionRepositoryInterface $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(SubscriptionRepositoryInterface::class);
        $this->useCase = new GetSubscriptionUseCase($this->repository);
    }

    public function testGetSubscriptionSuccess(): void
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

        $request = new GetSubscriptionRequest(1);
        $response = $this->useCase->execute($request);

        $this->assertInstanceOf(SubscriptionResponse::class, $response);
        $this->assertEquals(1, $response->id);
        $this->assertEquals('user-123', $response->userId);
    }

    public function testGetSubscriptionNotFound(): void
    {
        $this->repository->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Subscription not found.');

        $request = new GetSubscriptionRequest(999);
        $this->useCase->execute($request);
    }
}

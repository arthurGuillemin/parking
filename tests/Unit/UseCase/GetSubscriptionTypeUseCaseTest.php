<?php

namespace Tests\Unit\UseCase;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Application\UseCase\Owner\GetSubscriptionType\GetSubscriptionTypeUseCase;
use App\Application\UseCase\Owner\GetSubscriptionType\GetSubscriptionTypeRequest;
use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeResponse;
use App\Domain\Repository\SubscriptionTypeRepositoryInterface;
use App\Domain\Entity\SubscriptionType;

class GetSubscriptionTypeUseCaseTest extends TestCase
{
    private GetSubscriptionTypeUseCase $useCase;
    private MockObject|SubscriptionTypeRepositoryInterface $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(SubscriptionTypeRepositoryInterface::class);
        $this->useCase = new GetSubscriptionTypeUseCase($this->repository);
    }

    public function testGetSubscriptionTypeSuccess(): void
    {
        $type = new SubscriptionType(1, 1, 'Type 1', 'Desc 1');

        $this->repository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($type);

        $request = new GetSubscriptionTypeRequest(1);
        $response = $this->useCase->execute($request);

        $this->assertInstanceOf(AddSubscriptionTypeResponse::class, $response);
        $this->assertEquals(1, $response->id);
        $this->assertEquals('Type 1', $response->name);
    }

    public function testGetSubscriptionTypeNotFound(): void
    {
        $this->repository->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Subscription type not found.');

        $request = new GetSubscriptionTypeRequest(999);
        $this->useCase->execute($request);
    }
}

<?php

namespace Tests\Unit\UseCase;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Application\UseCase\Owner\ListSubscriptionTypes\ListSubscriptionTypesUseCase;
use App\Application\UseCase\Owner\ListSubscriptionTypes\ListSubscriptionTypesRequest;
use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeResponse;
use App\Domain\Repository\SubscriptionTypeRepositoryInterface;
use App\Domain\Entity\SubscriptionType;

class ListSubscriptionTypesUseCaseTest extends TestCase
{
    private ListSubscriptionTypesUseCase $useCase;
    private MockObject|SubscriptionTypeRepositoryInterface $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(SubscriptionTypeRepositoryInterface::class);
        $this->useCase = new ListSubscriptionTypesUseCase($this->repository);
    }

    public function testListTypesSuccess(): void
    {
        $type1 = new SubscriptionType(1, 1, 'Type 1', 'Desc 1');
        $type2 = new SubscriptionType(2, 1, 'Type 2', 'Desc 2');

        $this->repository->expects($this->once())
            ->method('findAll')
            ->willReturn([$type1, $type2]);

        $request = new ListSubscriptionTypesRequest(1);
        $response = $this->useCase->execute($request);

        $this->assertCount(2, $response);
        $this->assertInstanceOf(AddSubscriptionTypeResponse::class, $response[0]);
        $this->assertEquals(1, $response[0]->id);
    }
}

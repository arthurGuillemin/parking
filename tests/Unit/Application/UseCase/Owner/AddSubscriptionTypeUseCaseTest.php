<?php
namespace Unit\Application\UseCase\Owner;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeUseCase;
use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeRequest;
use App\Domain\Repository\SubscriptionTypeRepositoryInterface;
use App\Domain\Entity\SubscriptionType;
use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeResponse;

class AddSubscriptionTypeUseCaseTest extends TestCase
{
    public function testExecuteReturnsSavedType()
    {
        $repo = $this->createMock(SubscriptionTypeRepositoryInterface::class);
        $type = new SubscriptionType(1, 1, 'Premium', 'desc');
        $repo->expects($this->once())
            ->method('save')
            ->willReturn($type);
        $useCase = new AddSubscriptionTypeUseCase($repo);
        $request = new AddSubscriptionTypeRequest(1, 'Premium', 'desc');
        $result = $useCase->execute($request);
        $this->assertInstanceOf(AddSubscriptionTypeResponse::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals(1, $result->parkingId);
        $this->assertEquals('Premium', $result->name);
        $this->assertEquals('desc', $result->description);
    }
}

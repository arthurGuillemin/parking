<?php
namespace Unit\Application\UseCase\Owner;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeUseCase;
use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeRequest;
use App\Domain\Repository\SubscriptionTypeRepositoryInterface;
use App\Domain\Entity\SubscriptionType;

class AddSubscriptionTypeUseCaseTest extends TestCase
{
    public function testExecuteReturnsSavedType()
    {
        $repo = $this->createMock(SubscriptionTypeRepositoryInterface::class);
        $type = $this->createMock(SubscriptionType::class);
        $repo->expects($this->once())
            ->method('save')
            ->willReturn($type);
        $useCase = new AddSubscriptionTypeUseCase($repo);
        $request = new AddSubscriptionTypeRequest(1, 'Premium', 'desc');
        $result = $useCase->execute($request);
        $this->assertSame($type, $result);
    }
}

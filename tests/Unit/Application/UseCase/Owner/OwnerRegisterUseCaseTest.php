<?php
namespace Unit\Application\UseCase\Owner;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\Owner\Register\OwnerRegisterUseCase;
use App\Application\UseCase\Owner\Register\OwnerRegisterRequest;
use App\Domain\Repository\OwnerRepositoryInterface;
use App\Domain\Entity\Owner;

class OwnerRegisterUseCaseTest extends TestCase
{
    public function testExecuteReturnsSavedOwner()
    {
        $repo = $this->createMock(OwnerRepositoryInterface::class);
        $repo->method('findByEmail')->willReturn(null);
        $owner = $this->createMock(Owner::class);
        $repo->method('save')->willReturn($owner);
        $useCase = new OwnerRegisterUseCase($repo);
        $request = new OwnerRegisterRequest('James@example.com', 'password123', 'James', 'Lebron');
        $result = $useCase->execute($request);
        $this->assertSame($owner, $result);
    }
    public function testExecuteThrowsIfEmailExists()
    {
        $repo = $this->createMock(OwnerRepositoryInterface::class);
        $repo->method('findByEmail')->willReturn($this->createMock(Owner::class));
        $useCase = new OwnerRegisterUseCase($repo);
        $request = new OwnerRegisterRequest('James@example.com', 'password123', 'James', 'Lebron');
        $this->expectException(\InvalidArgumentException::class);
        $useCase->execute($request);
    }
    public function testExecuteThrowsIfPasswordTooShort()
    {
        $repo = $this->createMock(OwnerRepositoryInterface::class);
        $repo->method('findByEmail')->willReturn(null);
        $useCase = new OwnerRegisterUseCase($repo);
        $request = new OwnerRegisterRequest('James@example.com', 'short', 'James', 'Lebron');
        $this->expectException(\InvalidArgumentException::class);
        $useCase->execute($request);
    }
    public function testExecuteThrowsIfEmailInvalid()
    {
        $repo = $this->createMock(OwnerRepositoryInterface::class);
        $repo->method('findByEmail')->willReturn(null);
        $useCase = new OwnerRegisterUseCase($repo);
        $request = new OwnerRegisterRequest('invalid', 'password123', 'James', 'Lebron');
        $this->expectException(\InvalidArgumentException::class);
        $useCase->execute($request);
    }
}


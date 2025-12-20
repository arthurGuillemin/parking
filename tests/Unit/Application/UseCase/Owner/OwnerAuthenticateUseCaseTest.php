<?php
namespace Unit\Application\UseCase\Owner;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\Owner\Authenticate\OwnerAuthenticateUseCase;
use App\Application\UseCase\Owner\Authenticate\OwnerAuthenticateRequest;
use App\Domain\Repository\OwnerRepositoryInterface;
use App\Domain\Entity\Owner;

class OwnerAuthenticateUseCaseTest extends TestCase
{
    public function testExecuteReturnsOwnerOnValidCredentials()
    {
        $repo = $this->createMock(OwnerRepositoryInterface::class);
        $owner = $this->createMock(Owner::class);
        $repo->method('findByEmail')->willReturn($owner);
        $owner->method('getPassword')->willReturn(password_hash('password', PASSWORD_DEFAULT));
        $useCase = new OwnerAuthenticateUseCase($repo);
        $request = new OwnerAuthenticateRequest('test@example.com', 'password');
        $result = $useCase->execute($request);
        $this->assertSame($owner, $result);
    }
    public function testExecuteReturnsNullOnInvalidCredentials()
    {
        $repo = $this->createMock(OwnerRepositoryInterface::class);
        $owner = $this->createMock(Owner::class);
        $repo->method('findByEmail')->willReturn($owner);
        $owner->method('getPassword')->willReturn(password_hash('other', PASSWORD_DEFAULT));
        $useCase = new OwnerAuthenticateUseCase($repo);
        $request = new OwnerAuthenticateRequest('test@example.com', 'password');
        $result = $useCase->execute($request);
        $this->assertNull($result);
    }
    public function testExecuteReturnsNullIfOwnerNotFound()
    {
        $repo = $this->createMock(OwnerRepositoryInterface::class);
        $repo->method('findByEmail')->willReturn(null);
        $useCase = new OwnerAuthenticateUseCase($repo);
        $request = new OwnerAuthenticateRequest('test@example.com', 'password');
        $result = $useCase->execute($request);
        $this->assertNull($result);
    }
}


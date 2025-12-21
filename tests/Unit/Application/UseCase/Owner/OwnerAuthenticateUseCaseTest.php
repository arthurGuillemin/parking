<?php
namespace Unit\Application\UseCase\Owner;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\Owner\Authenticate\OwnerAuthenticateUseCase;
use App\Application\DTO\LoginResponse;
use App\Domain\Repository\OwnerRepositoryInterface;
use App\Domain\Entity\Owner;
use App\Domain\Security\PasswordHasherInterface;
use App\Domain\Auth\TokenGeneratorInterface;

class OwnerAuthenticateUseCaseTest extends TestCase
{
    private $repo;
    private $passwordHasher;
    private $tokenGenerator;

    protected function setUp(): void
    {
        $this->repo = $this->createStub(OwnerRepositoryInterface::class);
        $this->passwordHasher = $this->createStub(PasswordHasherInterface::class);
        $this->tokenGenerator = $this->createStub(TokenGeneratorInterface::class);
    }

    private function createUseCase(): OwnerAuthenticateUseCase
    {
        return new OwnerAuthenticateUseCase(
            $this->repo,
            $this->passwordHasher,
            $this->tokenGenerator
        );
    }

    public function testExecuteReturnsLoginResponseOnValidCredentials()
    {
        $owner = $this->createStub(Owner::class);
        $owner->method('getOwnerId')->willReturn('owner-123');
        $owner->method('getEmail')->willReturn('test@example.com');
        $owner->method('getPassword')->willReturn('hashed_password');

        $this->repo->method('findByEmail')->willReturn($owner);
        $this->passwordHasher->method('verify')->willReturn(true);
        $this->tokenGenerator->method('generate')->willReturn('mock_token');

        $useCase = $this->createUseCase();
        $result = $useCase->execute('test@example.com', 'password');

        $this->assertInstanceOf(LoginResponse::class, $result);
    }

    public function testExecuteReturnsNullOnInvalidCredentials()
    {
        $owner = $this->createStub(Owner::class);
        $owner->method('getPassword')->willReturn('hashed_password');

        $this->repo->method('findByEmail')->willReturn($owner);
        $this->passwordHasher->method('verify')->willReturn(false);

        $useCase = $this->createUseCase();
        $result = $useCase->execute('test@example.com', 'password');

        $this->assertNull($result);
    }

    public function testExecuteReturnsNullIfOwnerNotFound()
    {
        $this->repo->method('findByEmail')->willReturn(null);

        $useCase = $this->createUseCase();
        $result = $useCase->execute('test@example.com', 'password');

        $this->assertNull($result);
    }
}


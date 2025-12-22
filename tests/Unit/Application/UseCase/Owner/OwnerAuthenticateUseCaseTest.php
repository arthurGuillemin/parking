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
<<<<<<< HEAD
        $this->repo = $this->createStub(OwnerRepositoryInterface::class);
        $this->passwordHasher = $this->createStub(PasswordHasherInterface::class);
        $this->tokenGenerator = $this->createStub(TokenGeneratorInterface::class);
=======
        $repo = $this->createMock(OwnerRepositoryInterface::class);
        $owner = $this->createMock(Owner::class);
        $repo->method('findByEmail')->willReturn($owner);
        $owner->method('getPassword')->willReturn(password_hash('password', PASSWORD_DEFAULT));
        $mockHasher = $this->createMock(\App\Domain\Security\PasswordHasherInterface::class);
        $mockHasher->method('verify')->willReturn(true);
        $mockTokenGen = $this->createMock(\App\Domain\Auth\TokenGeneratorInterface::class);
        $useCase = new OwnerAuthenticateUseCase($repo, $mockHasher, $mockTokenGen);
        $request = new OwnerAuthenticateRequest('test@example.com', 'password');
        $result = $useCase->execute($request->email, $request->password);
        $this->assertInstanceOf(\App\Application\DTO\LoginResponse::class, $result);
>>>>>>> main
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
<<<<<<< HEAD
        $owner = $this->createStub(Owner::class);
        $owner->method('getPassword')->willReturn('hashed_password');

        $this->repo->method('findByEmail')->willReturn($owner);
        $this->passwordHasher->method('verify')->willReturn(false);

        $useCase = $this->createUseCase();
        $result = $useCase->execute('test@example.com', 'password');

=======
        $repo = $this->createMock(OwnerRepositoryInterface::class);
        $owner = $this->createMock(Owner::class);
        $repo->method('findByEmail')->willReturn($owner);
        $owner->method('getPassword')->willReturn(password_hash('other', PASSWORD_DEFAULT));
        $mockHasher = $this->createMock(\App\Domain\Security\PasswordHasherInterface::class);
        $mockHasher->method('verify')->willReturn(false);
        $mockTokenGen = $this->createMock(\App\Domain\Auth\TokenGeneratorInterface::class);
        $useCase = new OwnerAuthenticateUseCase($repo, $mockHasher, $mockTokenGen);
        $request = new OwnerAuthenticateRequest('test@example.com', 'password');
        $result = $useCase->execute($request->email, $request->password);
>>>>>>> main
        $this->assertNull($result);
    }

    public function testExecuteReturnsNullIfOwnerNotFound()
    {
<<<<<<< HEAD
        $this->repo->method('findByEmail')->willReturn(null);

        $useCase = $this->createUseCase();
        $result = $useCase->execute('test@example.com', 'password');

=======
        $repo = $this->createMock(OwnerRepositoryInterface::class);
        $repo->method('findByEmail')->willReturn(null);
        $mockHasher = $this->createMock(\App\Domain\Security\PasswordHasherInterface::class);
        $mockTokenGen = $this->createMock(\App\Domain\Auth\TokenGeneratorInterface::class);
        $useCase = new OwnerAuthenticateUseCase($repo, $mockHasher, $mockTokenGen);
        $request = new OwnerAuthenticateRequest('test@example.com', 'password');
        $result = $useCase->execute($request->email, $request->password);
>>>>>>> main
        $this->assertNull($result);
    }
}

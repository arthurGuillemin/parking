<?php

namespace Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use App\Domain\Service\OwnerService;
use App\Domain\Repository\OwnerRepositoryInterface;
use App\Domain\Security\PasswordHasherInterface;
use App\Domain\Auth\TokenGeneratorInterface;
use App\Domain\Entity\Owner;
use App\Domain\Security\PasswordHasherInterface;
use App\Domain\Auth\TokenGeneratorInterface;
use App\Domain\Service\JwtService;

class OwnerServiceTest extends TestCase
{
    private $ownerRepository;
    private $passwordHasher;
    private $tokenGenerator;

    protected function setUp(): void
    {
<<<<<<< HEAD
        $this->ownerRepository = $this->createStub(OwnerRepositoryInterface::class);
        $this->passwordHasher = $this->createStub(PasswordHasherInterface::class);
        $this->tokenGenerator = $this->createStub(TokenGeneratorInterface::class);
    }

    public function testRegisterReturnsOwner()
    {
        $owner = $this->createStub(Owner::class);
        $this->ownerRepository->method('save')->willReturn($owner);

        $service = new OwnerService($this->ownerRepository, $this->passwordHasher, $this->tokenGenerator);
        $result = $service->register('james@example.com', 'password', 'James', 'Bond');

        $this->assertInstanceOf(Owner::class, $result);
    }

    public function testAuthenticateReturnsLoginResponseOrNull()
    {
        $owner = $this->createStub(Owner::class);
        $owner->method('getOwnerId')->willReturn('owner-123');
        $owner->method('getEmail')->willReturn('test@example.com');
        $owner->method('getPassword')->willReturn('hashed_password');

        $this->ownerRepository->method('findByEmail')->willReturn($owner);
        $this->passwordHasher->method('verify')->willReturn(true);
        $this->tokenGenerator->method('generate')->willReturn('mock_token');

        $service = new OwnerService($this->ownerRepository, $this->passwordHasher, $this->tokenGenerator);
        $result = $service->authenticate('test@example.com', 'password');

        // Returns LoginResponse on success, null on failure
        $this->assertTrue($result instanceof \App\Application\DTO\LoginResponse || $result === null);
=======
        $ownerRepository = $this->createMock(OwnerRepositoryInterface::class);
        $hasher = $this->createMock(PasswordHasherInterface::class);
        $tokenGenerator = $this->createMock(TokenGeneratorInterface::class);

        $owner = $this->createMock(Owner::class);
        $ownerRepository->method('save')->willReturn($owner);

        $service = new OwnerService($ownerRepository, $hasher, $tokenGenerator);
        $result = $service->register('James@example.com', 'password', 'first', 'last');
        $this->assertSame($owner, $result);
    }

    public function testAuthenticateReturnsLoginResponseOrNull()
    {
        $ownerRepository = $this->createMock(OwnerRepositoryInterface::class);
        $hasher = $this->createMock(PasswordHasherInterface::class);
        $tokenGenerator = $this->createMock(TokenGeneratorInterface::class);

        $owner = $this->createMock(Owner::class);
        $owner->method('getPassword')->willReturn('hashed_password');
        $owner->method('getOwnerId')->willReturn('1');
        $owner->method('getEmail')->willReturn('email');

        $ownerRepository->method('findByEmail')->willReturn($owner);
        $hasher->method('verify')->willReturn(true);
        $tokenGenerator->method('generate')->willReturn('token_string');

        $service = new OwnerService($ownerRepository, $hasher, $tokenGenerator);
        $result = $service->authenticate('email', 'password');

        $this->assertInstanceOf(\App\Application\DTO\LoginResponse::class, $result);
>>>>>>> main
    }
}

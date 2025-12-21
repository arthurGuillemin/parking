<?php

namespace Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use App\Domain\Service\OwnerService;
use App\Domain\Repository\OwnerRepositoryInterface;
use App\Domain\Entity\Owner;
use App\Domain\Security\PasswordHasherInterface;
use App\Domain\Auth\TokenGeneratorInterface;
use App\Domain\Service\JwtService;

class OwnerServiceTest extends TestCase
{
    public function testRegisterReturnsOwner()
    {
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
    }
}

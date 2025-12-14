<?php

namespace App\Domain\Service;

use App\Application\UseCase\Owner\Authenticate\OwnerAuthenticateUseCase;
use App\Application\UseCase\Owner\Authenticate\OwnerAuthenticateRequest;
use App\Application\UseCase\Owner\Register\OwnerRegisterRequest;
use App\Application\UseCase\Owner\Register\OwnerRegisterUseCase;
use App\Domain\Entity\Owner;
use App\Domain\Repository\OwnerRepositoryInterface;
use App\Domain\Security\PasswordHasherInterface;
use App\Domain\Auth\TokenGeneratorInterface;

class OwnerService
{
    private OwnerRegisterUseCase $registerUseCase;
    private OwnerAuthenticateUseCase $authenticateUseCase;

    public function __construct(
        OwnerRepositoryInterface $ownerRepository,
        PasswordHasherInterface $passwordHasher,
        TokenGeneratorInterface $tokenGenerator
    ) {
        $this->registerUseCase = new OwnerRegisterUseCase($ownerRepository);
        $this->authenticateUseCase = new OwnerAuthenticateUseCase($ownerRepository, $passwordHasher, $tokenGenerator);
    }

    public function register(string $email, string $password, string $firstName, string $lastName): Owner
    {
        return $this->registerUseCase->execute(new OwnerRegisterRequest($email, $password, $firstName, $lastName));
    }

    public function authenticate(string $email, string $password): ?\App\Application\DTO\LoginResponse
    {
        return $this->authenticateUseCase->execute($email, $password);
    }
}

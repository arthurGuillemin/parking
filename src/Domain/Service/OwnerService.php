<?php

namespace App\Domain\Service;

use App\Application\UseCase\Owner\Authenticate\OwnerAuthenticateUseCase;
use App\Application\UseCase\Owner\Register\OwnerRegisterUseCase;
use App\Domain\Entity\Owner;
use App\Domain\Repository\OwnerRepositoryInterface;

class OwnerService
{
    private OwnerRepositoryInterface $ownerRepository;
    private OwnerRegisterUseCase $registerUseCase;
    private OwnerAuthenticateUseCase $authenticateUseCase;

    public function __construct(OwnerRepositoryInterface $ownerRepository)
    {
        $this->ownerRepository = $ownerRepository;
        $this->registerUseCase = new OwnerRegisterUseCase($ownerRepository);
        $this->authenticateUseCase = new OwnerAuthenticateUseCase($ownerRepository);
    }

    public function register(string $email, string $password, string $firstName, string $lastName): Owner
    {
        return $this->registerUseCase->execute($email, $password, $firstName, $lastName);
    }

    public function authenticate(string $email, string $password): ?Owner
    {
        return $this->authenticateUseCase->execute($email, $password);
    }
}

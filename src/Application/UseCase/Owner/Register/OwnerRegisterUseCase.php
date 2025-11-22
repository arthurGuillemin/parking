<?php

namespace App\Application\UseCase\Owner\Register;

use App\Domain\Entity\Owner;
use App\Domain\Repository\OwnerRepositoryInterface;
use Ramsey\Uuid\Uuid; // composer require ramsey/uuid

class OwnerRegisterUseCase
{
    private OwnerRepositoryInterface $ownerRepository;

    public function __construct(OwnerRepositoryInterface $ownerRepository)
    {
        $this->ownerRepository = $ownerRepository;
    }

    /**
     * Register a new owner account.
     *
     * @param OwnerRegisterRequest $request
     * @return Owner
     * @throws \InvalidArgumentException si l email est déjà utilisé
     */
    public function execute(OwnerRegisterRequest $request): Owner
    {
        if ($this->ownerRepository->findByEmail($request->email)) {
            throw new \InvalidArgumentException('Un compte avec cet email existe déjà.');
        }
        $passwordHash = password_hash($request->password, PASSWORD_DEFAULT);
        $owner = new Owner(
            Uuid::uuid4()->toString(),
            $request->email,
            $passwordHash,
            $request->firstName,
            $request->lastName,
            new \DateTimeImmutable()
        );
        return $this->ownerRepository->save($owner);
    }
}

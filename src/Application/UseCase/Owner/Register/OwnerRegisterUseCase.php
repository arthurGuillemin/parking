<?php

namespace App\Application\UseCase\Owner\Register;

use App\Domain\Entity\Owner;
use App\Domain\Repository\OwnerRepositoryInterface;
use Ramsey\Uuid\Uuid; 

class OwnerRegisterUseCase
{
    private OwnerRepositoryInterface $ownerRepository;

    public function __construct(OwnerRepositoryInterface $ownerRepository)
    {
        $this->ownerRepository = $ownerRepository;
    }

    public function execute(OwnerRegisterRequest $request): Owner
    {
        if ($this->ownerRepository->findByEmail($request->email)) {
            throw new \InvalidArgumentException('Un compte avec cet email existe déjà.');
        }
        if (strlen($request->password) < 8) {
            throw new \InvalidArgumentException('Le mot de passe doit contenir au moins 8 caractères.');
        }
        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("L'adresse email n est pas valide.");
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

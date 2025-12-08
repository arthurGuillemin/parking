<?php

namespace App\Application\UseCase\User\Register;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use Ramsey\Uuid\Uuid; // composer require ramsey/uuid

class UserRegisterUseCase
{
    private UserRepositoryInterface $ownerRepository;

    public function __construct(UserRepositoryInterface $ownerRepository)
    {
        $this->ownerRepository = $ownerRepository;
    }

    /**
     * Register a new owner account.
     *
     * @param UserRegisterRequest $request
     * @return User
     * @throws \InvalidArgumentException si l email est déjà utilisé
     */
    public function execute(UserRegisterRequest $request): User
    {
        if ($this->ownerRepository->findByEmail($request->email)) {
            throw new \InvalidArgumentException('Un compte avec cet email existe déjà.');
        }
        if (strlen($request->password) < 8) {
            throw new \InvalidArgumentException('Le mot de passe doit contenir au moins 8 caractères.');
        }
        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('L adresse email n est pas valide.');
        }
        $passwordHash = password_hash($request->password, PASSWORD_DEFAULT);
        $owner = new User(
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

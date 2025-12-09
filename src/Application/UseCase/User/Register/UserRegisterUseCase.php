<?php

namespace App\Application\UseCase\User\Register;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Security\PasswordHasherInterface;
use App\Domain\Service\UserRegistrationValidator;
use Ramsey\Uuid\Uuid;

class UserRegisterUseCase
{
    private UserRepositoryInterface $userRepository;
    private UserRegistrationValidator $validator;
    private PasswordHasherInterface $passwordHasher;

    public function __construct(
        UserRepositoryInterface $userRepository,
        UserRegistrationValidator $validator,
        PasswordHasherInterface $passwordHasher
    ) {
        $this->userRepository   = $userRepository;
        $this->validator        = $validator;
        $this->passwordHasher   = $passwordHasher;
    }

    /**
     *  Nouvel utilisateur 
     *
     * @throws \InvalidArgumentException si les données sont invalides ou si l'email est déjà utilisé
     */
    public function execute(UserRegisterRequest $request): User
    {
        // 1. Validation métier (email, mot de passe, prénom, nom)
        $errors = $this->validator->validate(
            email: $request->email,
            password: $request->password,
            firstName: $request->firstName,
            lastName: $request->lastName
        );

        if (!empty($errors)) {
            throw new \InvalidArgumentException(
                'Données d\'inscription invalides : ' . implode(' | ', $errors)
            );
        }

        // 2. Vérifier l'unicité de l'email
        if ($this->userRepository->findByEmail($request->email)) {
            throw new \InvalidArgumentException('Un compte avec cet email existe déjà.');
        }

        // 3. Hash du mot de passe (via l'interface de domaine)
        $passwordHash = $this->passwordHasher->hash($request->password);

        // 4. Création de l'entité User
        $user = new User(
            Uuid::uuid4()->toString(),
            $request->email,
            $passwordHash,
            $request->firstName,
            $request->lastName,
            new \DateTimeImmutable()
        );

        // 5. Persistance
        return $this->userRepository->save($user);
    }
}

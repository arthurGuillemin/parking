<?php
namespace App\Application\UseCase;

use App\Application\DTO\LoginResponse;
use App\Domain\Auth\TokenGeneratorInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Repository\OwnerRepositoryInterface;
use App\Domain\Security\PasswordHasherInterface;
use App\Domain\Service\JwtService;

class LoginUseCase
{
    private UserRepositoryInterface $userRepository;
    private OwnerRepositoryInterface $ownerRepository;
    private TokenGeneratorInterface $tokenGenerator;
    private PasswordHasherInterface $passwordHasher;

    public function __construct(
        UserRepositoryInterface $userRepository,
        OwnerRepositoryInterface $ownerRepository,
        TokenGeneratorInterface $tokenGenerator,
        PasswordHasherInterface $passwordHasher
    ) {
        $this->userRepository = $userRepository;
        $this->ownerRepository = $ownerRepository;
        $this->tokenGenerator = $tokenGenerator;
        $this->passwordHasher = $passwordHasher;
    }

    public function execute(string $email, string $password): ?LoginResponse
    {
        $found = $this->findEntityByEmail($email);
        if (!$found) {
            return null;
        }

        [$entity, $role] = $found;

        if (!$this->isValidUser($entity, $password)) {
            return null;
        }

        return $this->handleLogin($entity, $role);
    }

    private function findEntityByEmail(string $email): ?array
    {
        $owner = $this->ownerRepository->findByEmail($email);
        if ($owner) {
            return [$owner, 'owner'];
        }

        $user = $this->userRepository->findByEmail($email);
        if ($user) {
            return [$user, 'user'];
        }

        return null;
    }

    private function isValidUser(object $entity, string $password): bool
    {
        return $entity && $this->passwordHasher->verify($password, $entity->getPassword());
    }

    private function handleLogin(object $entity, string $role): LoginResponse
    {
        $userId = $role === 'owner' ? $entity->getOwnerId() : $entity->getUserId();

        $payload = [
            'user_id' => $userId,
            'email' => $entity->getEmail(),
            'role' => $role,
        ];

        $access = $this->tokenGenerator->generate(array_merge($payload, ['type' => 'access']));
        $refresh = $this->tokenGenerator->generate(array_merge($payload, ['type' => 'refresh']));

        return new LoginResponse(
            $access,
            $refresh,
            JwtService::ACCESS_TOKEN_TTL,
            $role,
            $entity->getFirstName(),
            $entity->getLastName()
        );
    }

}

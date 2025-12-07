<?php
namespace App\Application\UseCase;

use App\Application\DTO\LoginResponse;
use App\Domain\Auth\TokenGeneratorInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Repository\OwnerRepositoryInterface;
use App\Domain\Service\JwtService;

class LoginUseCase
{
    private UserRepositoryInterface $userRepository;
    private OwnerRepositoryInterface $ownerRepository;
    private TokenGeneratorInterface $tokenGenerator;

    public function __construct(UserRepositoryInterface $userRepository, OwnerRepositoryInterface $ownerRepository, TokenGeneratorInterface $tokenGenerator)
    {
        $this->userRepository = $userRepository;
        $this->ownerRepository = $ownerRepository;
        $this->tokenGenerator = $tokenGenerator;
    }

    public function execute(string $email, string $password): ?LoginResponse
    {
        $owner = $this->ownerRepository->findByEmail($email);
        if ($this->isValidUser($owner, $password)) {
            return $this->handleLogin($owner, 'owner');
        }

        $user = $this->userRepository->findByEmail($email);
        if ($this->isValidUser($user, $password)) {
            return $this->handleLogin($user, 'user');
        }

        return null;
    }

    private function isValidUser($entity, string $password): bool
    {
        return $entity && password_verify($password, $entity->getPassword());
    }

    private function handleLogin($entity, string $role): LoginResponse
    {
        $payload = [
            'user_id' => $role === 'owner' ? $entity->getOwnerId() : $entity->getUserId(),
            'email' => $entity->getEmail(),
            'role' => $role,
        ];
        $token = $this->tokenGenerator->generate(array_merge($payload, ['type' => 'access']));
        $expiresIn = JwtService::ACCESS_TOKEN_TTL;
        $refreshToken = $this->tokenGenerator->generate(array_merge($payload, ['type' => 'refresh']));
        setcookie('refresh_token', $refreshToken, [
            'expires' => time() + JwtService::REFRESH_TOKEN_TTL,
            'httponly' => true,
            'samesite' => 'Lax',
            'path' => '/',
        ]);
        return new LoginResponse($token, $expiresIn);
    }
}

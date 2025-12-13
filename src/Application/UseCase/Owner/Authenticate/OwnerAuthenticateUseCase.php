<?php

namespace App\Application\UseCase\Owner\Authenticate;

use App\Application\DTO\LoginResponse;
use App\Domain\Repository\OwnerRepositoryInterface;
use App\Domain\Security\PasswordHasherInterface;
use App\Domain\Auth\TokenGeneratorInterface;
use App\Domain\Service\JwtService;

class OwnerAuthenticateUseCase
{
    private OwnerRepositoryInterface $ownerRepository;
    private PasswordHasherInterface $passwordHasher;
    private TokenGeneratorInterface $tokenGenerator;

    public function __construct(
        OwnerRepositoryInterface $ownerRepository,
        PasswordHasherInterface $passwordHasher,
        TokenGeneratorInterface $tokenGenerator
    ) {
        $this->ownerRepository = $ownerRepository;
        $this->passwordHasher = $passwordHasher;
        $this->tokenGenerator = $tokenGenerator;
    }

    public function execute(string $email, string $password): ?LoginResponse
    {
        $owner = $this->ownerRepository->findByEmail($email);
        if (!$owner) {
            return null;
        }

        if (!$this->passwordHasher->verify($password, $owner->getPassword())) {
            return null;
        }

        $payload = [
            'user_id' => $owner->getOwnerId(),
            'email' => $owner->getEmail(),
            'role' => 'owner',
        ];

        $access = $this->tokenGenerator->generate(array_merge($payload, ['type' => 'access']));
        $refresh = $this->tokenGenerator->generate(array_merge($payload, ['type' => 'refresh']));

        setcookie('refresh_token', $refresh, [
            'expires' => time() + JwtService::REFRESH_TOKEN_TTL,
            'httponly' => true,
            'samesite' => 'Lax',
            'path' => '/',
        ]);

        return new LoginResponse($access, JwtService::ACCESS_TOKEN_TTL);
    }
}

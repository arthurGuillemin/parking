<?php

namespace App\Interface\Controller;

use App\Domain\Service\OwnerService;
use Exception;

class OwnerController
{
    private OwnerService $ownerService;

    public function __construct(OwnerService $ownerService)
    {
        $this->ownerService = $ownerService;
    }

    public function register(array $data): array
    {
        if (empty($data['email']) || empty($data['password']) || empty($data['firstName']) || empty($data['lastName'])) {
            throw new Exception('Champs requis manquants');
        }
        $owner = $this->ownerService->register(
            $data['email'],
            $data['password'],
            $data['firstName'],
            $data['lastName']
        );
        return [
            'id' => $owner->getOwnerId(),
            'email' => $owner->getEmail(),
            'firstName' => $owner->getFirstName(),
            'lastName' => $owner->getLastName(),
        ];
    }

    public function login(array $data): ?array
    {
        if (empty($data['email']) || empty($data['password'])) {
            throw new Exception('Missing email or password');
        }
        $owner = $this->ownerService->authenticate($data['email'], $data['password']);
        if ($owner) {
            return [
                'id' => $owner->getOwnerId(),
                'email' => $owner->getEmail(),
                'firstName' => $owner->getFirstName(),
                'lastName' => $owner->getLastName(),
            ];
        }
        return null;
    }
}


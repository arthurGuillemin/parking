<?php

namespace App\Application\UseCase\Owner\Authenticate;

use App\Domain\Entity\Owner;
use App\Domain\Repository\OwnerRepositoryInterface;
use App\Application\UseCase\Owner\Authenticate\OwnerAuthenticateRequest;

class OwnerAuthenticateUseCase
{
    private OwnerRepositoryInterface $ownerRepository;

    public function __construct(OwnerRepositoryInterface $ownerRepository)
    {
        $this->ownerRepository = $ownerRepository;
    }

    /**
     * Authenticate an owner by email and password.
     *
     * @param OwnerAuthenticateRequest $request
     * @return Owner|null
     */
    public function execute(OwnerAuthenticateRequest $request): ?Owner
    {
        $owner = $this->ownerRepository->findByEmail($request->email);
        if ($owner && password_verify($request->password, $owner->getPassword())) {
            return $owner;
        }
        return null;
    }
}

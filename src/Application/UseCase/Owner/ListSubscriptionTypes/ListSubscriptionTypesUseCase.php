<?php

namespace App\Application\UseCase\Owner\ListSubscriptionTypes;

use App\Domain\Repository\SubscriptionTypeRepositoryInterface;
use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeResponse;

class ListSubscriptionTypesUseCase
{
    private SubscriptionTypeRepositoryInterface $repository;

    public function __construct(SubscriptionTypeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(ListSubscriptionTypesRequest $request): array
    {
        // Note: For now we return all types. Future: filter by parkingId if implemented in repo.
        $types = $this->repository->findAll();

        return array_map(function ($type) {
            return new AddSubscriptionTypeResponse(
                $type->getSubscriptionTypeId(),
                $type->getParkingId(),
                $type->getName(),
                $type->getDescription()
            );
        }, $types);
    }
}

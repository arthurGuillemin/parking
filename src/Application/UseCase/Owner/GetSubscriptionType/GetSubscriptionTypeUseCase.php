<?php

namespace App\Application\UseCase\Owner\GetSubscriptionType;

use App\Domain\Repository\SubscriptionTypeRepositoryInterface;
use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeResponse;

class GetSubscriptionTypeUseCase
{
    private SubscriptionTypeRepositoryInterface $repository;

    public function __construct(SubscriptionTypeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(GetSubscriptionTypeRequest $request): AddSubscriptionTypeResponse
    {
        $type = $this->repository->findById($request->id);

        if (!$type) {
            throw new \RuntimeException("Subscription type not found.");
        }

        return new AddSubscriptionTypeResponse(
            $type->getSubscriptionTypeId(),
            $type->getParkingId(),
            $type->getName(),
            $type->getDescription(),
            0.0
        );
    }
}

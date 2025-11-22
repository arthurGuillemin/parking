<?php

namespace App\Domain\Service;

use App\Domain\Repository\SubscriptionTypeRepositoryInterface;
use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeUseCase;
use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeRequest;
use App\Domain\Entity\SubscriptionType;

class SubscriptionTypeService
{
    private SubscriptionTypeRepositoryInterface $subscriptionTypeRepository;
    private AddSubscriptionTypeUseCase $addSubscriptionTypeUseCase;

    public function __construct(SubscriptionTypeRepositoryInterface $subscriptionTypeRepository)
    {
        $this->subscriptionTypeRepository = $subscriptionTypeRepository;
        $this->addSubscriptionTypeUseCase = new AddSubscriptionTypeUseCase($subscriptionTypeRepository);
    }

    public function addSubscriptionType(AddSubscriptionTypeRequest $request): SubscriptionType
    {
        return $this->addSubscriptionTypeUseCase->execute($request);
    }
}


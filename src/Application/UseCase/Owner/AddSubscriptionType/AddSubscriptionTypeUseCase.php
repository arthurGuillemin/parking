<?php

namespace App\Application\UseCase\Owner\AddSubscriptionType;

use App\Domain\Entity\SubscriptionType;
use App\Domain\Repository\SubscriptionTypeRepositoryInterface;

class AddSubscriptionTypeUseCase
{
    private SubscriptionTypeRepositoryInterface $subscriptionTypeRepository;

    public function __construct(SubscriptionTypeRepositoryInterface $subscriptionTypeRepository)
    {
        $this->subscriptionTypeRepository = $subscriptionTypeRepository;
    }

    /**
     * Add a new subscription type for a parking.
     *
     * @param AddSubscriptionTypeRequest $request
     * @return SubscriptionType
     */
    public function execute(AddSubscriptionTypeRequest $request): SubscriptionType
    {
        $type = new SubscriptionType(0, $request->parkingId, $request->name, $request->description);
        return $this->subscriptionTypeRepository->save($type);
    }
}


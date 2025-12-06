<?php

namespace App\Application\UseCase\Owner\AddSubscriptionType;

use App\Domain\Entity\SubscriptionType;
use App\Domain\Repository\SubscriptionTypeRepositoryInterface;
use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeResponse;

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
     * @return AddSubscriptionTypeResponse
     */
    public function execute(AddSubscriptionTypeRequest $request): AddSubscriptionTypeResponse
    {
        $type = new SubscriptionType(0, $request->parkingId, $request->name, $request->description);
        $savedType = $this->subscriptionTypeRepository->save($type);

        return new AddSubscriptionTypeResponse(
            $savedType->getSubscriptionTypeId(),
            $savedType->getParkingId(),
            $savedType->getName(),
            $savedType->getDescription()
        );
    }
}

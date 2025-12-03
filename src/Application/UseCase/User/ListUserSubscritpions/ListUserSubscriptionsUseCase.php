<?php

namespace App\Application\UseCase\User\ListUserSubscriptions;

use App\Domain\Repository\SubscriptionRepositoryInterface;

class ListUserSubscriptionsUseCase
{
    private SubscriptionRepositoryInterface $subscriptionRepository;

    public function __construct(SubscriptionRepositoryInterface $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * List all active subscriptions for a user.
     *
     * @param ListUserSubscriptionsRequest $request
     * @return array
     */
    public function execute(ListUserSubscriptionsRequest $request): array
    {
        return $this->subscriptionRepository->findByUserId($request->userId);
    }
}
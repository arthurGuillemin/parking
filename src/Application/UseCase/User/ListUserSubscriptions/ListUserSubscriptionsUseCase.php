<?php

namespace App\Application\UseCase\User\ListUserSubscriptions;

use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Application\DTO\Response\SubscriptionResponse;

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
        $subscriptions = $this->subscriptionRepository->findByUserId($request->userId);

        return array_map(function ($sub) {
            return new SubscriptionResponse(
                $sub->getSubscriptionId(),
                $sub->getUserId(),
                $sub->getParkingId(),
                $sub->getTypeId(),
                $sub->getStartDate()->format('Y-m-d H:i:s'),
                $sub->getEndDate()?->format('Y-m-d H:i:s'),
                $sub->getStatus(),
                $sub->getMonthlyPrice()
            );
        }, $subscriptions);
    }
}
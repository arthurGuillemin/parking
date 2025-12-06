<?php

namespace App\Application\UseCase\User\GetSubscription;

use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Application\DTO\Response\SubscriptionResponse;

class GetSubscriptionUseCase
{
    private SubscriptionRepositoryInterface $subscriptionRepository;

    public function __construct(SubscriptionRepositoryInterface $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function execute(GetSubscriptionRequest $request): SubscriptionResponse
    {
        $subscription = $this->subscriptionRepository->findById($request->id);

        if (!$subscription) {
            throw new \RuntimeException("Subscription not found.");
        }

        return new SubscriptionResponse(
            $subscription->getSubscriptionId(),
            $subscription->getUserId(),
            $subscription->getParkingId(),
            $subscription->getTypeId(),
            $subscription->getStartDate()->format('Y-m-d H:i:s'),
            $subscription->getEndDate()?->format('Y-m-d H:i:s'),
            $subscription->getStatus(),
            $subscription->getMonthlyPrice()
        );
    }
}

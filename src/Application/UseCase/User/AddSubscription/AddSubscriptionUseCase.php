<?php

namespace App\Application\UseCase\User\AddSubscription;

use App\Domain\Entity\Subscription;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use DateTimeImmutable;

class AddSubscriptionUseCase
{
    private SubscriptionRepositoryInterface $subscriptionRepository;

    public function __construct(SubscriptionRepositoryInterface $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * Subscribe a user to a subscription type for a parking.
     *
     * @param AddSubscriptionRequest $request
     * @return Subscription
     * @throws \InvalidArgumentException if duration is invalid
     */
    public function execute(AddSubscriptionRequest $request): Subscription
    {
        // Validate duration: minimum 1 month, maximum 1 year
        $minEndDate = $request->startDate->add(new \DateInterval('P1M'));
        
        if ($request->endDate === null) {
            // Default to 1 year if no end date provided
            $request->endDate = $request->startDate->add(new \DateInterval('P1Y'));
        }

        if ($request->endDate < $minEndDate) {
            throw new \InvalidArgumentException('Subscription duration must be at least 1 month.');
        }

        if ($request->endDate > $request->startDate->add(new \DateInterval('P1Y'))) {
            throw new \InvalidArgumentException('Subscription duration cannot exceed 1 year.');
        }

        // Create subscription
        $subscription = new Subscription(
            0, // ID sera généré par la BDD
            $request->userId,
            $request->parkingId,
            $request->typeId,
            $request->startDate,
            $request->endDate,
            'active',
            $request->monthlyPrice
        );

        return $this->subscriptionRepository->save($subscription);
    }
}
<?php

namespace App\Application\UseCase\User\AddSubscription;

use App\Domain\Entity\Subscription;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Application\DTO\Response\SubscriptionResponse;
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
     * @return SubscriptionResponse
     * @throws \InvalidArgumentException 
     */
    public function execute(AddSubscriptionRequest $request): SubscriptionResponse
    {
        // Validate duration: minimum 1 month, maximum 1 year
        $minEndDate = $request->startDate->add(new \DateInterval('P1M'));

        if ($request->endDate === null) {
            // Set par défaut à 1 an si non spécifié
            $request->endDate = $request->startDate->add(new \DateInterval('P1Y'));
        }

        if ($request->endDate < $minEndDate) {
            throw new \InvalidArgumentException('Subscription duration must be at least 1 month.');
        }

        if ($request->endDate > $request->startDate->add(new \DateInterval('P1Y'))) {
            throw new \InvalidArgumentException('Subscription duration cannot exceed 1 year.');
        }

        $subscription = new Subscription(
            0,
            $request->userId,
            $request->parkingId,
            $request->typeId,
            $request->startDate,
            $request->endDate,
            'active',
            $request->monthlyPrice
        );

        $savedSubscription = $this->subscriptionRepository->save($subscription);

        return new SubscriptionResponse(
            $savedSubscription->getSubscriptionId(),
            $savedSubscription->getUserId(),
            $savedSubscription->getParkingId(),
            $savedSubscription->getTypeId(),
            $savedSubscription->getStartDate()->format('Y-m-d H:i:s'),
            $savedSubscription->getEndDate()?->format('Y-m-d H:i:s'),
            $savedSubscription->getStatus(),
            $savedSubscription->getMonthlyPrice()
        );
    }
}
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
     * Abonner un utilisateur à un type d'abonnement pour un parking.
     *
     * @param AddSubscriptionRequest $request
     * @return SubscriptionResponse
     * @throws \InvalidArgumentException 
     */
    public function execute(AddSubscriptionRequest $request): SubscriptionResponse
    {
        // Vérifier la durée: minimum 1 mois, maximum 1 an
        $minEndDate = $request->startDate->add(new \DateInterval('P1M'));

        if ($request->endDate === null) {
            $request->endDate = $request->startDate->add(new \DateInterval('P1Y'));
        }

        if ($request->endDate < $minEndDate) {
            throw new \InvalidArgumentException('La durée de l\'abonnement doit être d\'au moins 1 mois.');
        }

        if ($request->endDate > $request->startDate->add(new \DateInterval('P1Y'))) {
            throw new \InvalidArgumentException('La durée de l\'abonnement ne peut pas excéder 1 an.');
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
<?php

namespace App\Application\UseCase\User\ListUserSubscriptions;

use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Application\DTO\Response\SubscriptionResponse;

use App\Domain\Repository\ParkingRepositoryInterface;

class ListUserSubscriptionsUseCase
{
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private ParkingRepositoryInterface $parkingRepository;

    public function __construct(SubscriptionRepositoryInterface $subscriptionRepository, ParkingRepositoryInterface $parkingRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->parkingRepository = $parkingRepository;
    }

    /**
     * Afficher tous les abonnements actifs d'un utilisateur.
     *
     * @param ListUserSubscriptionsRequest $request
     * @return array
     */
    public function execute(ListUserSubscriptionsRequest $request): array
    {
        $subscriptions = $this->subscriptionRepository->findByUserId($request->userId);

        return array_map(function ($sub) {
            $parking = $this->parkingRepository->findById($sub->getParkingId());
            $parkingName = $parking ? $parking->getName() : 'Inconnu';

            return new SubscriptionResponse(
                $sub->getSubscriptionId(),
                $sub->getUserId(),
                $sub->getParkingId(),
                $sub->getTypeId(),
                $sub->getStartDate()->format('Y-m-d H:i:s'),
                $sub->getEndDate()?->format('Y-m-d H:i:s'),
                $sub->getStatus(),
                $sub->getMonthlyPrice(),
                $parkingName
            );
        }, $subscriptions);
    }
}
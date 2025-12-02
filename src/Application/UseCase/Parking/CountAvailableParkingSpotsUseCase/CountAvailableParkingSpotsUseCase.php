<?php

namespace App\Application\UseCase\Parking\CountAvailableParkingSpotsUseCase;

use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Repository\SubscriptionRepositoryInterface;

class CountAvailableParkingSpotsUseCase
{
    private ParkingRepositoryInterface $parkingRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private SubscriptionRepositoryInterface $subscriptionRepository;

    public function __construct(
        ParkingRepositoryInterface $parkingRepository,
        ReservationRepositoryInterface $reservationRepository,
        SubscriptionRepositoryInterface $subscriptionRepository
    ) {
        $this->parkingRepository = $parkingRepository;
        $this->reservationRepository = $reservationRepository;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function execute(CountAvailableParkingSpotsRequest $request): int
    {
        $parking = $this->parkingRepository->findById($request->parkingId);
        if (!$parking) {
            throw new \InvalidArgumentException('Parking non trouvé.');
        }

        $at = $request->at;
        $totalCapacity = $parking->getTotalCapacity();

        $reservationCount = count(
            $this->reservationRepository->findForParkingBetween($request->parkingId, $at, $at)
        );

        $year = (int)$at->format('Y');
        $month = (int)$at->format('m');
        $subscriptions = $this->subscriptionRepository->findByParkingIdAndMonth($request->parkingId, $year, $month);

        $activeSubscriptionCount = array_reduce(
            $subscriptions,
            fn($count, $subscription) => $count + ($subscription->getStartDate() <= $at && ($subscription->getEndDate() === null || $subscription->getEndDate() >= $at) && (!method_exists($subscription, 'isActiveAt') || $subscription->isActiveAt($at)) ? 1 : 0), 0
        );
        return max(0, $totalCapacity - ($reservationCount + $activeSubscriptionCount));
    }
}

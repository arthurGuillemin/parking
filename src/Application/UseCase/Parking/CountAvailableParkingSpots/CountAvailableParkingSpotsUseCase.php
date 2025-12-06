<?php

namespace App\Application\UseCase\Parking\CountAvailableParkingSpots;

use App\Application\DTO\Parking\CountAvailableParkingSpots\CountAvailableParkingSpotsRequest;
use App\Application\DTO\Parking\CountAvailableParkingSpots\CountAvailableParkingSpotsResponse;
use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Repository\SubscriptionRepositoryInterface;

class CountAvailableParkingSpotsUseCase {
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

    public function execute(CountAvailableParkingSpotsRequest $request): CountAvailableParkingSpotsResponse
    {
        $parking = $this->parkingRepository->findById($request->parkingId);
        if (!$parking) {
            throw new \InvalidArgumentException('Parking non disponible.');
        }

        $at = $request->at;
        $totalCapacity = $parking->getTotalCapacity();

        $reservationCount = $this->countReservations($request->parkingId, $at);
        $activeSubscriptionCount = $this->countActiveSubscriptions($request->parkingId, $at);

        $availableSpots = max(0, $totalCapacity - ($reservationCount + $activeSubscriptionCount));

        return new CountAvailableParkingSpotsResponse(
            $request->parkingId,
            $totalCapacity,
            $availableSpots,
            $at
        );
    }

    private function countReservations(int $parkingId, \DateTimeImmutable $at): int
    {
        return count(
            $this->reservationRepository->findForParkingBetween($parkingId, $at, $at)
        );
    }

    private function countActiveSubscriptions(int $parkingId, \DateTimeImmutable $at): int
    {
        $year = (int) $at->format('Y');
        $month = (int) $at->format('m');
        $subscriptions = $this->subscriptionRepository->findByParkingIdAndMonth($parkingId, $year, $month);

        return array_reduce(
            $subscriptions,
            fn($count, $subscription) => $count + (
                $subscription->getStartDate() <= $at &&
                ($subscription->getEndDate() === null || $subscription->getEndDate() >= $at) &&
                (!method_exists($subscription, 'isActiveAt') || $subscription->isActiveAt($at))
                    ? 1 : 0
                ),
            0
        );
    }
}

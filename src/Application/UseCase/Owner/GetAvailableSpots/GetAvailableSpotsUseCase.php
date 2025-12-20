<?php

namespace App\Application\UseCase\Owner\GetAvailableSpots;

use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Repository\SubscriptionRepositoryInterface;

class GetAvailableSpotsUseCase
{
    private ParkingRepositoryInterface $parkingRepository;
    private ParkingSessionRepositoryInterface $parkingSessionRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private SubscriptionRepositoryInterface $subscriptionRepository;

    public function __construct(
        ParkingRepositoryInterface $parkingRepository,
        ParkingSessionRepositoryInterface $parkingSessionRepository,
        ReservationRepositoryInterface $reservationRepository,
        SubscriptionRepositoryInterface $subscriptionRepository
    ) {
        $this->parkingRepository = $parkingRepository;
        $this->parkingSessionRepository = $parkingSessionRepository;
        $this->reservationRepository = $reservationRepository;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function execute(GetAvailableSpotsRequest $request): int
    {
        $parking = $this->parkingRepository->findById($request->parkingId);
        if (!$parking) {
            throw new \InvalidArgumentException('Parking non disponible.');
        }
        $occupied = $this->countOccupiedSessions($request);
        $occupied['count'] += $this->countActiveReservations($request, $occupied['sessionReservationIds']);
        $occupied['count'] += $this->countActiveSubscriptions($request, $occupied['sessionUserIds']);
        return max(0, $parking->getTotalCapacity() - $occupied['count']);
    }

    private function countOccupiedSessions(GetAvailableSpotsRequest $request): array
    {
        $sessions = $this->parkingSessionRepository->findByParkingId($request->parkingId);
        $occupied = 0;
        $sessionUserIds = [];
        $sessionReservationIds = [];
        foreach ($sessions as $session) {
            $entry = $session->getEntryDateTime();
            $exit = $session->getExitDateTime();
            if ($entry <= $request->at && (is_null($exit) || $exit > $request->at)) {
                $occupied++;
                $sessionUserIds[] = $session->getUserId();
                if (method_exists($session, 'getReservationId') && $session->getReservationId()) {
                    $sessionReservationIds[] = $session->getReservationId();
                }
            }
        }
        return [
            'count' => $occupied,
            'sessionUserIds' => $sessionUserIds,
            'sessionReservationIds' => $sessionReservationIds
        ];
    }

    private function countActiveReservations(GetAvailableSpotsRequest $request, array $sessionReservationIds): int
    {
        $reservations = $this->reservationRepository->findForParkingBetween(
            $request->parkingId,
            $request->at,
            $request->at
        );
        $occupied = 0;
        foreach ($reservations as $reservation) {
            if (
                ($reservation->getStatus() === 'confirmed' || $reservation->getStatus() === 'pending') &&
                $reservation->getStartDateTime() <= $request->at &&
                $reservation->getEndDateTime() > $request->at &&
                !in_array($reservation->getReservationId(), $sessionReservationIds)
            ) {
                $occupied++;
            }
        }
        return $occupied;
    }

    private function countActiveSubscriptions(GetAvailableSpotsRequest $request, array $sessionUserIds): int
    {
        $year = (int) $request->at->format('Y');
        $month = (int) $request->at->format('m');
        $subscriptions = $this->subscriptionRepository->findByParkingIdAndMonth($request->parkingId, $year, $month);
        $occupied = 0;
        foreach ($subscriptions as $subscription) {
            if (
                $subscription->getStatus() === 'active' &&
                $subscription->getStartDate() <= $request->at &&
                ($subscription->getEndDate() === null || $subscription->getEndDate() >= $request->at) &&
                !in_array($subscription->getUserId(), $sessionUserIds)
            ) {
                $occupied++;
            }
        }
        return $occupied;
    }
}

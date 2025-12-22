<?php

namespace App\Application\UseCase\Owner\GetAvailableSpots;

use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Domain\Service\SubscriptionCoverageService;

class GetAvailableSpotsUseCase
{
    private ParkingRepositoryInterface $parkingRepository;
    private ParkingSessionRepositoryInterface $parkingSessionRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private SubscriptionCoverageService $coverageService;

    public function __construct(
        ParkingRepositoryInterface $parkingRepository,
        ParkingSessionRepositoryInterface $parkingSessionRepository,
        ReservationRepositoryInterface $reservationRepository,
        SubscriptionRepositoryInterface $subscriptionRepository,
        SubscriptionCoverageService $coverageService
    ) {
        $this->parkingRepository = $parkingRepository;
        $this->parkingSessionRepository = $parkingSessionRepository;
        $this->reservationRepository = $reservationRepository;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->coverageService = $coverageService;
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
            // Vérifier la validité générale (dates & statut) ET la couverture de la période
            if (
                $subscription->getStatus() === 'active' &&
                !in_array($subscription->getUserId(), $sessionUserIds) &&
                $this->coverageService->isDateTimeCovered($subscription, $request->at)
            ) {
                $occupied++;
            }
        }
        return $occupied;
    }
}

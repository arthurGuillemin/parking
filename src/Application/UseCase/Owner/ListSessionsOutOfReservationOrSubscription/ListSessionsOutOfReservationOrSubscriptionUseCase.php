<?php

namespace App\Application\UseCase\Owner\ListSessionsOutOfReservationOrSubscription;

use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Domain\Service\SubscriptionCoverageService;
use App\Domain\Entity\ParkingSession;

class ListSessionsOutOfReservationOrSubscriptionUseCase
{
    private ParkingSessionRepositoryInterface $parkingSessionRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private SubscriptionCoverageService $coverageService;

    public function __construct(
        ParkingSessionRepositoryInterface $parkingSessionRepository,
        ReservationRepositoryInterface $reservationRepository,
        SubscriptionRepositoryInterface $subscriptionRepository,
        SubscriptionCoverageService $coverageService
    ) {
        $this->parkingSessionRepository = $parkingSessionRepository;
        $this->reservationRepository = $reservationRepository;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->coverageService = $coverageService;
    }

    /**
     * List parking sessions out of reservation or subscription slots for a parking.
     *
     * @param ListSessionsOutOfReservationOrSubscriptionRequest $request
     * @return ParkingSession[]
     */
    public function execute(ListSessionsOutOfReservationOrSubscriptionRequest $request): array
    {
        $sessions = $this->parkingSessionRepository->findByParkingId($request->parkingId);
        $result = [];

        foreach ($sessions as $session) {
            $userId = $session->getUserId();
            $entry = $session->getEntryDateTime();
            $exit = $session->getExitDateTime() ?? new \DateTimeImmutable();

            // Vérifier les réservations
            $hasReservation = array_filter(
                $this->reservationRepository->findByUserId($userId),
                fn($r) => $r->getParkingId() === $request->parkingId
                    && $r->getStartDateTime() <= $entry
                    && $r->getEndDateTime() >= $exit
            );

            // Vérifier les abonnements avec les créneaux horaires
            $hasSubscription = false;
            if (!$hasReservation) {
                $subscriptions = $this->subscriptionRepository->findByUserId($userId);
                foreach ($subscriptions as $subscription) {
                    if (
                        $subscription->getParkingId() === $request->parkingId
                        && $this->coverageService->isDateTimeCovered($subscription, $entry)
                    ) {
                        $hasSubscription = true;
                        break;
                    }
                }
            }

            if (!$hasReservation && !$hasSubscription) {
                $result[] = $session;
            }
        }

        return $result;
    }
}

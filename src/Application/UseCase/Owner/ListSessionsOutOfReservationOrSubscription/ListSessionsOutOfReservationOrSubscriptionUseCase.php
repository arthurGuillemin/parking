<?php

namespace App\Application\UseCase\Owner\ListSessionsOutOfReservationOrSubscription;

use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Domain\Entity\ParkingSession;

class ListSessionsOutOfReservationOrSubscriptionUseCase
{
    private ParkingSessionRepositoryInterface $parkingSessionRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private SubscriptionRepositoryInterface $subscriptionRepository;

    public function __construct(
        ParkingSessionRepositoryInterface $parkingSessionRepository,
        ReservationRepositoryInterface $reservationRepository,
        SubscriptionRepositoryInterface $subscriptionRepository
    ) {
        $this->parkingSessionRepository = $parkingSessionRepository;
        $this->reservationRepository = $reservationRepository;
        $this->subscriptionRepository = $subscriptionRepository;
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
            $hasReservation = false;
            $hasSubscription = false;

            // Vérifier si une réservation couvre la session
            $reservations = $this->reservationRepository->findByUserId($userId);
            foreach ($reservations as $reservation) {
                if ($reservation->getParkingId() === $request->parkingId &&
                    $reservation->getStartDateTime() <= $entry &&
                    $reservation->getEndDateTime() >= $exit) {
                    $hasReservation = true;
                    break;
                }
            }

            // Vérifier si un abonnement couvre la session
            $subscriptions = $this->subscriptionRepository->findByUserId($userId);
            foreach ($subscriptions as $subscription) {
                if ($subscription->getParkingId() === $request->parkingId &&
                    $subscription->getStartDate() <= $entry &&
                    ($subscription->getEndDate() === null || $subscription->getEndDate() >= $exit)) {
                    $hasSubscription = true;
                    break;
                }
            }

            if (!$hasReservation && !$hasSubscription) {
                $result[] = $session;
            }
        }
        return $result;
    }
}


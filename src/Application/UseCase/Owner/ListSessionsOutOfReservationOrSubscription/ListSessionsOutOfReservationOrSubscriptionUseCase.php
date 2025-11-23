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

            $hasReservation = array_filter(
                $this->reservationRepository->findByUserId($userId),
                fn($r) => $r->getParkingId() === $request->parkingId  && $r->getStartDateTime() <= $entry && $r->getEndDateTime() >= $exit
            );

            $hasSubscription = array_filter(
                $this->subscriptionRepository->findByUserId($userId),
                fn($s) => $s->getParkingId() === $request->parkingId && $s->getStartDate() <= $entry && ($s->getEndDate() === null || ($session->getExitDateTime() === null ? $s->getEndDate() >= new \DateTimeImmutable() : $s->getEndDate() >= $exit))
            );

            if (!$hasReservation && !$hasSubscription) {
                $result[] = $session;
            }
        }
        return $result;
    }
}


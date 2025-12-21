<?php

namespace App\Application\UseCase\User\EnterParking;

use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Domain\Entity\ParkingSession;
use App\Application\DTO\Response\ParkingSessionResponse;
use DateTimeImmutable;
use RuntimeException;

class EnterParkingUseCase
{
    private ParkingSessionRepositoryInterface $sessionRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private SubscriptionRepositoryInterface $subscriptionRepository;

    public function __construct(
        ParkingSessionRepositoryInterface $sessionRepository,
        ReservationRepositoryInterface $reservationRepository,
        SubscriptionRepositoryInterface $subscriptionRepository
    ) {
        $this->sessionRepository = $sessionRepository;
        $this->reservationRepository = $reservationRepository;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function execute(EnterParkingRequest $request): ParkingSessionResponse
    {
        // 1. Check if user already inside
        $activeSession = $this->sessionRepository->findActiveSessionByUserId($request->userId);
        if ($activeSession) {
            throw new RuntimeException("Vous êtes déjà stationné dans un parking.");
        }

        // 2. Validate Access (Reservation OR Subscription)
        $hasAccess = false;
        $reservationId = $request->reservationId;

        if ($reservationId) {
            $reservation = $this->reservationRepository->findById($reservationId);
            if (!$reservation || $reservation->getUserId() !== $request->userId || $reservation->getParkingId() !== $request->parkingId) {
                throw new RuntimeException("Réservation invalide.");
            }
            // Check time validity (allow entry 15 mins before)
            $now = new DateTimeImmutable();
            if ($now < $reservation->getStartDateTime()->modify('-30 minutes')) {
                throw new RuntimeException("Il est trop tôt pour entrer (max 30 min avant).");
            }
            if ($now > $reservation->getEndDateTime()) {
                throw new RuntimeException("Cette réservation est expirée.");
            }
            $hasAccess = true;
        } else {
            // Check Subscription
            $subscriptions = $this->subscriptionRepository->findActiveByUserId($request->userId);
            foreach ($subscriptions as $sub) {
                if ($sub->getParkingId() === $request->parkingId) {
                    $hasAccess = true;
                    break;
                }
            }
        }

        if (!$hasAccess) {
            throw new RuntimeException("Accès refusé. Aucune réservation ou abonnement valide trouvé pour ce parking.");
        }

        // 3. Create Session
        $session = new ParkingSession(
            0,
            $request->userId,
            $request->parkingId,
            $reservationId,
            new DateTimeImmutable(),
            null,
            null,
            false
        );

        $savedSession = $this->sessionRepository->save($session);

        return new ParkingSessionResponse($savedSession);
    }
}

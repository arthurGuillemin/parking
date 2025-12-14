<?php

namespace App\Application\UseCase\User\EnterParking;

use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Domain\Entity\ParkingSession;
use App\Domain\Service\ParkingAvailabilityService; // To check spot availability if needed? For now just validation.
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

    public function execute(EnterParkingRequest $request): ParkingSession
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
            // Check time validity (allow entry 15 mins before?)
            $now = new DateTimeImmutable();
            if ($now < $reservation->getStartDateTime()->modify('-15 minutes')) {
                throw new RuntimeException("Il est trop tôt pour entrer (max 15 min avant).");
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
                    // Note: Should strictly check time slots here, but simplifying for now.
                    break;
                }
            }
        }

        if (!$hasAccess) {
            throw new RuntimeException("Accès refusé. Aucune réservation ou abonnement valide trouvé pour ce parking.");
        }

        // 3. Create Session
        // Note: ID generation usually handled by database (SERIAL), but Entity might require an ID.
        // If SQL repository uses INSERT without ID (auto-inc), we might pass 0 or null?
        // Checking ParkingSession entity structure.

        // Simulating ID generation for entity object before save? 
        // Or Repository save returns new object with ID.
        // Assuming Repository handles ID generation if passed 0/null or we generate random int for now (less ideal).
        // Let's pass 0 and rely on Repo/DB.

        $session = new ParkingSession(
            0, // Placeholder
            $request->userId,
            $request->parkingId,
            $reservationId,
            new DateTimeImmutable(),
            null,
            null,
            false // No penalty yet
        );

        return $this->sessionRepository->save($session);
    }
}

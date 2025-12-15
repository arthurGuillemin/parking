<?php

namespace App\Application\UseCase\User\EnterParking;

use App\Application\DTO\Response\ParkingSessionResponse;
use App\Domain\Entity\ParkingSession;
use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;

class EnterParkingUseCase
{
    private ReservationRepositoryInterface $reservationRepository;
    private ParkingSessionRepositoryInterface $parkingSessionRepository;
    private ParkingRepositoryInterface $parkingRepository;

    public function __construct(
        ReservationRepositoryInterface $reservationRepository,
        ParkingSessionRepositoryInterface $parkingSessionRepository,
        ParkingRepositoryInterface $parkingRepository
    ) {
        $this->reservationRepository = $reservationRepository;
        $this->parkingSessionRepository = $parkingSessionRepository;
        $this->parkingRepository = $parkingRepository;
    }

    public function execute(EnterParkingRequest $request): ParkingSessionResponse
    {
        // vérifier si le user est déjà dans un parking 
        $existingSession = $this->parkingSessionRepository->findActiveSessionByUserId($request->userId);
        if ($existingSession) {
            throw new \Exception("User is already in a parking session.");
        }

        // vérifier si le parking est disponible
        // "Un utilisateur ne peut entrer que s’il dispose d’une réservation active"
        $now = new \DateTimeImmutable();
        $reservation = $this->reservationRepository->findActiveReservation($request->userId, $request->parkingId, $now);

        if (!$reservation) {
            throw new \Exception("No active reservation found for this parking at the current time.");
        }

        // Créer une session de parking
        $session = new ParkingSession(
            0,
            $request->userId,
            $request->parkingId,
            $reservation->getReservationId(),
            $now,
            null,
            null,
            false
        );

        $savedSession = $this->parkingSessionRepository->save($session);

        return new ParkingSessionResponse($savedSession);
    }
}

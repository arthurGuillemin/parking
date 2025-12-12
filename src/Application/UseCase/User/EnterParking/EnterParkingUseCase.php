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
        // 1. Check if user is already inside
        $existingSession = $this->parkingSessionRepository->findActiveSessionByUserId($request->userId);
        if ($existingSession) {
            throw new \Exception("User is already in a parking session.");
        }

        // 2. Check for Active Reservation
        // "Un utilisateur ne peut entrer ... que s’il dispose d’une réservation active"
        // Active means: Start <= Now <= End
        $now = new \DateTimeImmutable();
        $reservation = $this->reservationRepository->findActiveReservation($request->userId, $request->parkingId, $now);

        if (!$reservation) {
            throw new \Exception("No active reservation found for this parking at the current time.");
        }

        // 3. Create Parking Session
        $session = new ParkingSession(
            0,
            $request->userId,
            $request->parkingId,
            $reservation->getReservationId(),
            $now,
            null, // exitDateTime
            null, // finalAmount (calculated at exit)
            false // penaltyApplied
        );

        $savedSession = $this->parkingSessionRepository->save($session);

        return new ParkingSessionResponse($savedSession);
    }
}

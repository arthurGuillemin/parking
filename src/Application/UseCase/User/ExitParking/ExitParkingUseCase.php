<?php

namespace App\Application\UseCase\User\ExitParking;

use App\Application\DTO\Response\ParkingSessionResponse;
use App\Domain\Entity\ParkingSession;
use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;

class ExitParkingUseCase
{
    private ParkingSessionRepositoryInterface $parkingSessionRepository;
    private ReservationRepositoryInterface $reservationRepository;

    public function __construct(
        ParkingSessionRepositoryInterface $parkingSessionRepository,
        ReservationRepositoryInterface $reservationRepository
    ) {
        $this->parkingSessionRepository = $parkingSessionRepository;
        $this->reservationRepository = $reservationRepository;
    }

    public function execute(ExitParkingRequest $request): ParkingSessionResponse
    {
        // 1. Find Active Session
        $session = $this->parkingSessionRepository->findActiveSessionByUserId($request->userId);
        if (!$session) {
            throw new \Exception("User is not currently in a parking session.");
        }

        if ($session->getParkingId() !== $request->parkingId) {
            throw new \Exception("User is in a different parking.");
        }

        // 2. Get Reservation
        $reservationId = $session->getReservationId();
        if (!$reservationId) {
            // Handle case without reservation (e.g. ad-hoc entry if allowed, but here we cover reservation flow)
            // Default amount?
            $amount = 0.0;
        } else {
            $reservation = $this->reservationRepository->findById($reservationId);
            if ($reservation) {
                $amount = $reservation->getCalculatedAmount() ?? 0.0;

                // 3. Close Reservation and Release Spot
                // Requirement: "redevenant disponible pour d’autres utilisateurs"
                // So we update the reservation end time to Now (releasing the slot).
                // Requirement: "se voit quand même facturé sur la totalité"
                // We keep the calculated amount.
                $reservation->complete(new \DateTimeImmutable(), $amount);
                $this->reservationRepository->save($reservation);
            } else {
                $amount = 0.0;
            }
        }

        // 4. Close Session
        $now = new \DateTimeImmutable();
        $session->close($now, $amount);
        $savedSession = $this->parkingSessionRepository->save($session);

        return new ParkingSessionResponse($savedSession);
    }
}

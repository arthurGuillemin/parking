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
        // vérifier si le user est dans un parking
        $session = $this->parkingSessionRepository->findActiveSessionByUserId($request->userId);
        if (!$session) {
            throw new \Exception("User is not currently in a parking session.");
        }

        if ($session->getParkingId() !== $request->parkingId) {
            throw new \Exception("User is in a different parking.");
        }

        // récupérer la réservation
        $reservationId = $session->getReservationId();
        if (!$reservationId) {
            $amount = 0.0;
        } else {
            $reservation = $this->reservationRepository->findById($reservationId);
            if ($reservation) {
                $amount = $reservation->getCalculatedAmount() ?? 0.0;

                // fermer la réservation et libére le parking
                $reservation->complete(new \DateTimeImmutable(), $amount);
                $this->reservationRepository->save($reservation);
            } else {
                $amount = 0.0;
            }
        }

        // fermer la session
        $now = new \DateTimeImmutable();
        $session->close($now, $amount);
        $savedSession = $this->parkingSessionRepository->save($session);

        return new ParkingSessionResponse($savedSession);
    }
}

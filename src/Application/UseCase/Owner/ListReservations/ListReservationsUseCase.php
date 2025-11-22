<?php

namespace App\Application\UseCase\Owner\ListReservations;

use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Entity\Reservation;

class ListReservationsUseCase
{
    private ReservationRepositoryInterface $reservationRepository;

    public function __construct(ReservationRepositoryInterface $reservationRepository)
    {
        $this->reservationRepository = $reservationRepository;
    }

    /**
     * List reservations for a parking, optionally filtered by date range.
     *
     * @param ListReservationsRequest $request
     * @return Reservation[]
     */
    public function execute(ListReservationsRequest $request): array
    {
        if ($request->start && $request->end) {
            return $this->reservationRepository->findForParkingBetween(
                $request->parkingId,
                $request->start,
                $request->end
            );
        }
        // Si pas de filtre, on retourne toutes les réservations du parking
        // (Supposons qu'il existe une méthode findByParkingId sinon on peut utiliser une période très large)
        $epoch = new \DateTimeImmutable('1970-01-01');
        $future = new \DateTimeImmutable('2100-01-01');
        return $this->reservationRepository->findForParkingBetween(
            $request->parkingId,
            $epoch,
            $future
        );
    }
}


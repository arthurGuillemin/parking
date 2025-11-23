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
        return $this->reservationRepository->findAllByParkingId($request->parkingId);
    }
}


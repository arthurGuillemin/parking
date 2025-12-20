<?php

namespace App\Application\UseCase\User\ListUserReservations;

use App\Domain\Repository\ReservationRepositoryInterface;
use App\Application\DTO\Response\ReservationResponse;

class ListUserReservationsUseCase
{
    private ReservationRepositoryInterface $reservationRepository;

    public function __construct(ReservationRepositoryInterface $reservationRepository)
    {
        $this->reservationRepository = $reservationRepository;
    }

    public function execute(ListUserReservationsRequest $request): array
    {
        // Assuming repository has findByUserId or we filter existing list
        // Let's check repository interface next tool call. 
        // For now, I'll rely on findByUserId being needed.
        $reservations = $this->reservationRepository->findByUserId($request->userId);

        return array_map(function ($reservation) {
            return new ReservationResponse(
                $reservation->getReservationId(),
                $reservation->getUserId(),
                $reservation->getParkingId(),
                $reservation->getStartDateTime()->format('Y-m-d H:i:s'),
                $reservation->getEndDateTime()->format('Y-m-d H:i:s'),
                $reservation->getStatus(),
                $reservation->getCalculatedAmount()
            );
        }, $reservations);
    }
}

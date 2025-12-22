<?php

namespace App\Application\UseCase\User\ListUserReservations;

use App\Domain\Repository\ReservationRepositoryInterface;
use App\Application\DTO\Response\ReservationResponse;

use App\Domain\Repository\ParkingRepositoryInterface;

class ListUserReservationsUseCase
{
    private ReservationRepositoryInterface $reservationRepository;
    private ParkingRepositoryInterface $parkingRepository;

    public function __construct(ReservationRepositoryInterface $reservationRepository, ParkingRepositoryInterface $parkingRepository)
    {
        $this->reservationRepository = $reservationRepository;
        $this->parkingRepository = $parkingRepository;
    }

    public function execute(ListUserReservationsRequest $request): array
    {
        $reservations = $this->reservationRepository->findByUserId($request->userId);

        return array_map(function ($reservation) {
            $parking = $this->parkingRepository->findById($reservation->getParkingId());
            $parkingName = $parking ? $parking->getName() : 'Inconnu';
            return new ReservationResponse($reservation, $parkingName);
        }, $reservations);
    }
}

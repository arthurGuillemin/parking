<?php

namespace App\Domain\Service;

use App\Domain\Repository\ReservationRepositoryInterface;
use App\Application\UseCase\Owner\ListReservations\ListReservationsUseCase;
use App\Application\UseCase\Owner\ListReservations\ListReservationsRequest;

class ReservationService
{
    private ReservationRepositoryInterface $reservationRepository;
    private ListReservationsUseCase $listReservationsUseCase;

    public function __construct(ReservationRepositoryInterface $reservationRepository)
    {
        $this->reservationRepository = $reservationRepository;
        $this->listReservationsUseCase = new ListReservationsUseCase($reservationRepository);
    }

    public function listReservations(ListReservationsRequest $request): array
    {
        return $this->listReservationsUseCase->execute($request);
    }
}


<?php

namespace App\Domain\Service;

use App\Domain\Repository\ReservationRepositoryInterface;
use App\Application\UseCase\Owner\ListReservations\ListReservationsUseCase;
use App\Application\UseCase\Owner\ListReservations\ListReservationsRequest;

class ReservationService
{
    private ReservationRepositoryInterface $reservationRepository;
    private ListReservationsUseCase $listReservationsUseCase;
    private \App\Application\UseCase\User\CreateReservation\CreateReservationUseCase $createReservationUseCase;

    public function __construct(
        ReservationRepositoryInterface $reservationRepository,
        \App\Application\UseCase\User\CreateReservation\CreateReservationUseCase $createReservationUseCase
    ) {
        $this->reservationRepository = $reservationRepository;
        $this->createReservationUseCase = $createReservationUseCase;
        $this->listReservationsUseCase = new ListReservationsUseCase($reservationRepository);
    }

    public function listReservations(ListReservationsRequest $request): array
    {
        return $this->listReservationsUseCase->execute($request);
    }

    public function createReservation(int $parkingId, string $userId, \DateTimeImmutable $start, \DateTimeImmutable $end): \App\Domain\Entity\Reservation
    {
        return $this->createReservationUseCase->execute($parkingId, $userId, $start, $end);
    }
}


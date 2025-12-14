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
        // Keep list usage as explicit/internal or inject it? UseCases usually standalone.
        // But the previous code instantiated it. Let's start injecting properly as per previous refactor.
        // Except I don't want to break existing 'new ListReservationsUseCase' without full refactor if feasible.
        // The user asked for "quick refactor" of Service DI earlier.
        // To stay consistent: I should inject CreateReservationUseCase.
        $this->createReservationUseCase = $createReservationUseCase;
        $this->listReservationsUseCase = new ListReservationsUseCase($reservationRepository); // Legacy, kept for now unless I inject it too.
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


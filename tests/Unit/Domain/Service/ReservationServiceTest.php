<?php

namespace Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use App\Domain\Service\ReservationService;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Application\UseCase\User\CreateReservation\CreateReservationUseCase;
use App\Application\UseCase\Owner\ListReservations\ListReservationsRequest;

class ReservationServiceTest extends TestCase
{
    public function testListReservationsReturnsArray()
    {
<<<<<<< HEAD
        $reservationRepository = $this->createStub(ReservationRepositoryInterface::class);
        $createReservationUseCase = $this->createStub(CreateReservationUseCase::class);

        $reservationRepository->method('findAllByParkingId')->willReturn([]);

=======
        $reservationRepository = $this->createMock(ReservationRepositoryInterface::class);
        $reservationRepository->method('findAllByParkingId')->willReturn([1, 2, 3]);
        $createReservationUseCase = $this->createMock(\App\Application\UseCase\User\CreateReservation\CreateReservationUseCase::class);
>>>>>>> main
        $service = new ReservationService($reservationRepository, $createReservationUseCase);
        $request = new ListReservationsRequest(1, new \DateTimeImmutable('2025-11-28'), new \DateTimeImmutable('2025-11-29'));
        $result = $service->listReservations($request);

        $this->assertIsArray($result);
    }
}

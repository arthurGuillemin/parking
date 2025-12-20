<?php

namespace Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use App\Domain\Service\ReservationService;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Application\UseCase\Owner\ListReservations\ListReservationsRequest;

class ReservationServiceTest extends TestCase
{
    public function testListReservationsReturnsArray()
    {
        $reservationRepository = $this->createMock(ReservationRepositoryInterface::class);
        $reservationRepository->method('findAllByParkingId')->willReturn([1,2,3]);
        $service = new ReservationService($reservationRepository);
        $request = new ListReservationsRequest(1, new \DateTimeImmutable('2025-11-28'), new \DateTimeImmutable('2025-11-29'));
        $result = $service->listReservations($request);
        $this->assertIsArray($result);
    }
}

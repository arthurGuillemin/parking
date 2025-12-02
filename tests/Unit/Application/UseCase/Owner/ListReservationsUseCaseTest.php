<?php
namespace Unit\Application\UseCase\Owner;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\Owner\ListReservations\ListReservationsUseCase;
use App\Application\UseCase\Owner\ListReservations\ListReservationsRequest;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Entity\Reservation;

class ListReservationsUseCaseTest extends TestCase
{
    public function testExecuteReturnsReservations()
    {
        $repo = $this->createMock(ReservationRepositoryInterface::class);
        $reservation = $this->createMock(Reservation::class);
        $repo->method('findAllByParkingId')->willReturn([$reservation]);
        $useCase = new ListReservationsUseCase($repo);
        $request = new ListReservationsRequest(1);
        $result = $useCase->execute($request);
        $this->assertIsArray($result);
        $this->assertSame($reservation, $result[0]);
    }
}


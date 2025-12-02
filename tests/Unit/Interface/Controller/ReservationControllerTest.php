<?php
namespace Unit\Interface\Controller;

use PHPUnit\Framework\TestCase;
use App\Interface\Controller\ReservationController;
use App\Domain\Service\ReservationService;
use App\Application\UseCase\Owner\ListReservations\ListReservationsRequest;
use App\Domain\Entity\Reservation;

class ReservationControllerTest extends TestCase
{
    public function testListByParkingReturnsArray()
    {
        $mockService = $this->createMock(ReservationService::class);
        $mockReservation = $this->createMock(Reservation::class);
        $mockReservation->method('getReservationId')->willReturn(1);
        $mockReservation->method('getUserId')->willReturn('user');
        $mockReservation->method('getParkingId')->willReturn(2);
        $mockReservation->method('getStartDateTime')->willReturn(new \DateTimeImmutable('2025-11-29 10:00:00'));
        $mockReservation->method('getEndDateTime')->willReturn(new \DateTimeImmutable('2025-11-29 12:00:00'));
        $mockReservation->method('getStatus')->willReturn('done');
        $mockReservation->method('getCalculatedAmount')->willReturn(10.0);
        $mockReservation->method('getFinalAmount')->willReturn(12.0);
        $mockService->method('listReservations')->willReturn([$mockReservation]);
        $controller = new ReservationController($mockService);
        $data = ['parkingId' => 2];
        $result = $controller->listByParking($data);
        $this->assertEquals([
            [
                'id' => 1,
                'userId' => 'user',
                'parkingId' => 2,
                'startDateTime' => '2025-11-29 10:00:00',
                'endDateTime' => '2025-11-29 12:00:00',
                'status' => 'done',
                'calculatedAmount' => 10.0,
                'finalAmount' => 12.0,
            ]
        ], $result);
    }
    public function testListByParkingThrowsOnMissingFields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $controller = new ReservationController($this->createMock(ReservationService::class));
        $controller->listByParking([]);
    }
}


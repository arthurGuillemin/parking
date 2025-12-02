<?php
namespace Unit\Interface\Controller;

use PHPUnit\Framework\TestCase;
use App\Interface\Controller\ParkingSessionController;
use App\Domain\Service\ParkingSessionService;
use App\Application\UseCase\Owner\ListParkingSessions\ListParkingSessionsRequest;
use App\Domain\Entity\ParkingSession;

class ParkingSessionControllerTest extends TestCase
{
    public function testListByParkingReturnsArray()
    {
        $mockService = $this->createMock(ParkingSessionService::class);
        $mockSession = $this->createMock(ParkingSession::class);
        $mockSession->method('getSessionId')->willReturn(1);
        $mockSession->method('getUserId')->willReturn('user');
        $mockSession->method('getParkingId')->willReturn(2);
        $mockSession->method('getReservationId')->willReturn(3);
        $mockSession->method('getEntryDateTime')->willReturn(new \DateTimeImmutable('2025-11-29 10:00:00'));
        $mockSession->method('getExitDateTime')->willReturn(null);
        $mockSession->method('getFinalAmount')->willReturn(20.0);
        $mockSession->method('isPenaltyApplied')->willReturn(false);
        $mockService->method('listParkingSessions')->willReturn([$mockSession]);
        $controller = new ParkingSessionController($mockService);
        $data = ['parkingId' => 2];
        $result = $controller->listByParking($data);
        $this->assertEquals([
            [
                'id' => 1,
                'userId' => 'user',
                'parkingId' => 2,
                'reservationId' => 3,
                'entryDateTime' => '2025-11-29 10:00:00',
                'exitDateTime' => null,
                'finalAmount' => 20.0,
                'penaltyApplied' => false,
            ]
        ], $result);
    }
    public function testListByParkingThrowsOnMissingFields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $controller = new ParkingSessionController($this->createMock(ParkingSessionService::class));
        $controller->listByParking([]);
    }
}


<?php
namespace Unit\Interface\Controller;

use PHPUnit\Framework\TestCase;
use App\Interface\Controller\SessionsOutOfReservationOrSubscriptionController;
use App\Domain\Service\SessionsOutOfReservationOrSubscriptionService;
use App\Application\UseCase\Owner\ListSessionsOutOfReservationOrSubscription\ListSessionsOutOfReservationOrSubscriptionRequest;
use App\Domain\Entity\ParkingSession;

class SessionsOutOfReservationOrSubscriptionControllerTest extends TestCase
{
    public function testListReturnsArray()
    {
        $mockService = $this->createMock(SessionsOutOfReservationOrSubscriptionService::class);
        $mockSession = $this->createMock(ParkingSession::class);
        $mockSession->method('getSessionId')->willReturn(1);
        $mockSession->method('getUserId')->willReturn('user');
        $mockSession->method('getParkingId')->willReturn(2);
        $mockSession->method('getReservationId')->willReturn(3);
        $mockSession->method('getEntryDateTime')->willReturn(new \DateTimeImmutable('2025-11-29 10:00:00'));
        $mockSession->method('getExitDateTime')->willReturn(null);
        $mockSession->method('getFinalAmount')->willReturn(20.0);
        $mockSession->method('isPenaltyApplied')->willReturn(false);
        $mockService->method('listSessions')->willReturn([$mockSession]);
        $controller = new SessionsOutOfReservationOrSubscriptionController($mockService);
        $data = ['parkingId' => 2];
        $result = $controller->list($data);
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
    public function testListThrowsOnMissingFields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $controller = new SessionsOutOfReservationOrSubscriptionController($this->createMock(SessionsOutOfReservationOrSubscriptionService::class));
        $controller->list([]);
    }
}


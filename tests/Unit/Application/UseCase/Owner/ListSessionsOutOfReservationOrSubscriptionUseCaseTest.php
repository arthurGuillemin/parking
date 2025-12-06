<?php
namespace Unit\Application\UseCase\Owner;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\Owner\ListSessionsOutOfReservationOrSubscription\ListSessionsOutOfReservationOrSubscriptionUseCase;
use App\Application\UseCase\Owner\ListSessionsOutOfReservationOrSubscription\ListSessionsOutOfReservationOrSubscriptionRequest;
use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Domain\Entity\ParkingSession;

class ListSessionsOutOfReservationOrSubscriptionUseCaseTest extends TestCase
{
    public function testExecuteReturnsSessions()
    {
        $sessionRepo = $this->createMock(ParkingSessionRepositoryInterface::class);
        $reservationRepo = $this->createMock(ReservationRepositoryInterface::class);
        $subscriptionRepo = $this->createMock(SubscriptionRepositoryInterface::class);
        $coverageService = $this->createMock(\App\Domain\Service\SubscriptionCoverageService::class);
        $session = $this->createMock(ParkingSession::class);
        $session->method('getUserId')->willReturn('user-uuid');
        $session->method('getEntryDateTime')->willReturn(new \DateTimeImmutable('-1 hour'));
        $session->method('getExitDateTime')->willReturn(null);
        $sessionRepo->method('findByParkingId')->willReturn([$session]);
        $reservationRepo->method('findByUserId')->willReturn([]);
        $subscriptionRepo->method('findByUserId')->willReturn([]);
        $useCase = new ListSessionsOutOfReservationOrSubscriptionUseCase($sessionRepo, $reservationRepo, $subscriptionRepo, $coverageService);
        $request = new ListSessionsOutOfReservationOrSubscriptionRequest(1);
        $result = $useCase->execute($request);
        $this->assertIsArray($result);
    }
}

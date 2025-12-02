<?php
namespace Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use App\Domain\Service\SessionsOutOfReservationOrSubscriptionService;
use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Application\UseCase\Owner\ListSessionsOutOfReservationOrSubscription\ListSessionsOutOfReservationOrSubscriptionRequest;

class SessionsOutOfReservationOrSubscriptionServiceTest extends TestCase
{
    public function testListSessionsDelegatesToUseCase()
    {
        $parkingSessionRepo = $this->createMock(ParkingSessionRepositoryInterface::class);
        $reservationRepo = $this->createMock(ReservationRepositoryInterface::class);
        $subscriptionRepo = $this->createMock(SubscriptionRepositoryInterface::class);
        $service = new SessionsOutOfReservationOrSubscriptionService($parkingSessionRepo, $reservationRepo, $subscriptionRepo);
        $request = new ListSessionsOutOfReservationOrSubscriptionRequest(1);
        $result = $service->listSessions($request);
        $this->assertIsArray($result);
    }
}

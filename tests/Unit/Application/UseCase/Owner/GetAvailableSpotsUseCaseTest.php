<?php
namespace Unit\Application\UseCase\Owner;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\Owner\GetAvailableSpots\GetAvailableSpotsUseCase;
use App\Application\UseCase\Owner\GetAvailableSpots\GetAvailableSpotsRequest;
use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Domain\Entity\Parking;
use App\Domain\Entity\ParkingSession;
use App\Domain\Entity\Reservation;
use App\Domain\Entity\Subscription;

class GetAvailableSpotsUseCaseTest extends TestCase
{
    public function testExecuteReturnsAvailableSpots()
    {
        $parkingRepo = $this->createMock(ParkingRepositoryInterface::class);
        $sessionRepo = $this->createMock(ParkingSessionRepositoryInterface::class);
        $reservationRepo = $this->createMock(ReservationRepositoryInterface::class);
        $subscriptionRepo = $this->createMock(SubscriptionRepositoryInterface::class);
        $parking = $this->createMock(Parking::class);
        $parking->method('getTotalCapacity')->willReturn(10);
        $parkingRepo->method('findById')->willReturn($parking);
        $session = $this->createMock(ParkingSession::class);
        $session->method('getEntryDateTime')->willReturn(new \DateTimeImmutable('-1 hour'));
        $session->method('getExitDateTime')->willReturn(null);
        $session->method('getUserId')->willReturn('user1');
        $session->method('getReservationId')->willReturn(null);
        $sessionRepo->method('findByParkingId')->willReturn([$session]);
        $reservationRepo->method('findForParkingBetween')->willReturn([]);
        $subscriptionRepo->method('findByParkingIdAndMonth')->willReturn([]);
        $useCase = new GetAvailableSpotsUseCase($parkingRepo, $sessionRepo, $reservationRepo, $subscriptionRepo);
        $request = new GetAvailableSpotsRequest(1, new \DateTimeImmutable());
        $result = $useCase->execute($request);
        $this->assertEquals(9, $result);
    }
    public function testExecuteWithActiveReservationBlocksSpot()
    {
        $parkingRepo = $this->createMock(ParkingRepositoryInterface::class);
        $sessionRepo = $this->createMock(ParkingSessionRepositoryInterface::class);
        $reservationRepo = $this->createMock(ReservationRepositoryInterface::class);
        $subscriptionRepo = $this->createMock(SubscriptionRepositoryInterface::class);
        $parking = $this->createMock(Parking::class);
        $parking->method('getTotalCapacity')->willReturn(5);
        $parkingRepo->method('findById')->willReturn($parking);
        $sessionRepo->method('findByParkingId')->willReturn([]);
        $reservation = $this->createMock(Reservation::class);
        $reservation->method('getStatus')->willReturn('active');
        $reservation->method('getStartDateTime')->willReturn(new \DateTimeImmutable('-1 hour'));
        $reservation->method('getEndDateTime')->willReturn(new \DateTimeImmutable('+1 hour'));
        $reservation->method('getReservationId')->willReturn(42);
        $reservationRepo->method('findForParkingBetween')->willReturn([$reservation]);
        $subscriptionRepo->method('findByParkingIdAndMonth')->willReturn([]);
        $useCase = new GetAvailableSpotsUseCase($parkingRepo, $sessionRepo, $reservationRepo, $subscriptionRepo);
        $request = new GetAvailableSpotsRequest(1, new \DateTimeImmutable());
        $result = $useCase->execute($request);
        $this->assertEquals(4, $result);
    }
    public function testExecuteWithActiveSubscriptionBlocksSpot()
    {
        $parkingRepo = $this->createMock(ParkingRepositoryInterface::class);
        $sessionRepo = $this->createMock(ParkingSessionRepositoryInterface::class);
        $reservationRepo = $this->createMock(ReservationRepositoryInterface::class);
        $subscriptionRepo = $this->createMock(SubscriptionRepositoryInterface::class);
        $parking = $this->createMock(Parking::class);
        $parking->method('getTotalCapacity')->willReturn(3);
        $parkingRepo->method('findById')->willReturn($parking);
        $sessionRepo->method('findByParkingId')->willReturn([]);
        $reservationRepo->method('findForParkingBetween')->willReturn([]);
        $subscription = $this->createMock(Subscription::class);
        $subscription->method('getStatus')->willReturn('active');
        $subscription->method('getStartDate')->willReturn(new \DateTimeImmutable('-1 month'));
        $subscription->method('getEndDate')->willReturn(new \DateTimeImmutable('+1 month'));
        $subscription->method('getUserId')->willReturn('user2');
        $subscriptionRepo->method('findByParkingIdAndMonth')->willReturn([$subscription]);
        $useCase = new GetAvailableSpotsUseCase($parkingRepo, $sessionRepo, $reservationRepo, $subscriptionRepo);
        $request = new GetAvailableSpotsRequest(1, new \DateTimeImmutable());
        $result = $useCase->execute($request);
        $this->assertEquals(2, $result);
    }
    public function testExecuteWithSessionAndReservationNoDoubleCount()
    {
        $parkingRepo = $this->createMock(ParkingRepositoryInterface::class);
        $sessionRepo = $this->createMock(ParkingSessionRepositoryInterface::class);
        $reservationRepo = $this->createMock(ReservationRepositoryInterface::class);
        $subscriptionRepo = $this->createMock(SubscriptionRepositoryInterface::class);
        $parking = $this->createMock(Parking::class);
        $parking->method('getTotalCapacity')->willReturn(2);
        $parkingRepo->method('findById')->willReturn($parking);
        $session = $this->createMock(ParkingSession::class);
        $session->method('getEntryDateTime')->willReturn(new \DateTimeImmutable('-1 hour'));
        $session->method('getExitDateTime')->willReturn(null);
        $session->method('getUserId')->willReturn('user3');
        $session->method('getReservationId')->willReturn(99);
        $sessionRepo->method('findByParkingId')->willReturn([$session]);
        $reservation = $this->createMock(Reservation::class);
        $reservation->method('getStatus')->willReturn('active');
        $reservation->method('getStartDateTime')->willReturn(new \DateTimeImmutable('-2 hour'));
        $reservation->method('getEndDateTime')->willReturn(new \DateTimeImmutable('+2 hour'));
        $reservation->method('getReservationId')->willReturn(99);
        $reservationRepo->method('findForParkingBetween')->willReturn([$reservation]);
        $subscriptionRepo->method('findByParkingIdAndMonth')->willReturn([]);
        $useCase = new GetAvailableSpotsUseCase($parkingRepo, $sessionRepo, $reservationRepo, $subscriptionRepo);
        $request = new GetAvailableSpotsRequest(1, new \DateTimeImmutable());
        $result = $useCase->execute($request);
        $this->assertEquals(1, $result);
    }
    public function testExecuteThrowsIfParkingNotFound()
    {
        $parkingRepo = $this->createMock(ParkingRepositoryInterface::class);
        $sessionRepo = $this->createMock(ParkingSessionRepositoryInterface::class);
        $reservationRepo = $this->createMock(ReservationRepositoryInterface::class);
        $subscriptionRepo = $this->createMock(SubscriptionRepositoryInterface::class);
        $parkingRepo->method('findById')->willReturn(null);
        $useCase = new GetAvailableSpotsUseCase($parkingRepo, $sessionRepo, $reservationRepo, $subscriptionRepo);
        $request = new GetAvailableSpotsRequest(1, new \DateTimeImmutable());
        $this->expectException(\InvalidArgumentException::class);
        $useCase->execute($request);
    }
}


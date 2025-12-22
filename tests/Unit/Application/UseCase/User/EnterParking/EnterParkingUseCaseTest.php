<?php

namespace Unit\Application\UseCase\User\EnterParking;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Application\UseCase\User\EnterParking\EnterParkingUseCase;
use App\Application\UseCase\User\EnterParking\EnterParkingRequest;
use App\Domain\Entity\Reservation;
use App\Domain\Entity\ParkingSession;
use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;

class EnterParkingUseCaseTest extends TestCase
{
    private EnterParkingUseCase $useCase;
    private MockObject|ParkingSessionRepositoryInterface $parkingSessionRepository;
    private MockObject|ReservationRepositoryInterface $reservationRepository;
    private MockObject|\App\Domain\Repository\SubscriptionRepositoryInterface $subscriptionRepository;

    protected function setUp(): void
    {
        $this->parkingSessionRepository = $this->createMock(ParkingSessionRepositoryInterface::class);
        $this->reservationRepository = $this->createMock(ReservationRepositoryInterface::class);
        $this->subscriptionRepository = $this->createMock(\App\Domain\Repository\SubscriptionRepositoryInterface::class);

        $this->useCase = new EnterParkingUseCase(
            $this->parkingSessionRepository,
            $this->reservationRepository,
            $this->subscriptionRepository
        );
    }

    public function testExecuteSuccess(): void
    {
        $request = new EnterParkingRequest('user-1', 1, 100); // 100 passed as reservationId

        // 1. Not inside
        $this->parkingSessionRepository->method('findActiveSessionByUserId')->willReturn(null);

        // 2. Active Reservation
        $reservation = new Reservation(
            100,
            'user-1',
            1,
            new \DateTimeImmutable('-1 hour'),
            new \DateTimeImmutable('+1 hour'),
            'pending',
            10.0,
            null
        );
        $this->reservationRepository->method('findById')->willReturn($reservation);

        // 3. Save Session
        $this->parkingSessionRepository->expects($this->once())
            ->method('save')
            ->willReturnCallback(function ($session) {
                // Mimic DB assigning ID
                return $session;
            });

        $response = $this->useCase->execute($request);

        $this->assertInstanceOf(\App\Application\DTO\Response\ParkingSessionResponse::class, $response);
        $this->assertEquals(100, $response->reservationId);
    }

    public function testExecuteFailsIfAlreadyInside(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Vous êtes déjà stationné dans un parking.");

        $request = new EnterParkingRequest('user-1', 1);

        $existingSession = $this->createMock(ParkingSession::class);
        $this->parkingSessionRepository->method('findActiveSessionByUserId')->willReturn($existingSession);

        $this->useCase->execute($request);
    }

    public function testExecuteFailsIfNoReservation(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Accès refusé. Aucune réservation ou abonnement valide trouvé pour ce parking.");

        $request = new EnterParkingRequest('user-1', 1);

        $this->parkingSessionRepository->method('findActiveSessionByUserId')->willReturn(null);
        $this->reservationRepository->method('findActiveReservation')->willReturn(null);

        $this->useCase->execute($request);
    }
}

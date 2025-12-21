<?php

namespace Unit\Application\UseCase\User\ExitParking;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\User\ExitParking\ExitParkingUseCase;
use App\Application\UseCase\User\ExitParking\ExitParkingRequest;
use App\Application\DTO\Response\ParkingSessionResponse;
use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Repository\InvoiceRepositoryInterface;
use App\Domain\Service\PricingService;
use App\Domain\Entity\ParkingSession;
use App\Domain\Entity\Reservation;
use App\Domain\Entity\Invoice;
use DateTimeImmutable;

class ExitParkingUseCaseTest extends TestCase
{
    public function testExitWithOverstayPenalty()
    {
        // Setup Mocks
        $sessionRepo = $this->createMock(ParkingSessionRepositoryInterface::class);
        $reservationRepo = $this->createMock(ReservationRepositoryInterface::class);
        $invoiceRepo = $this->createMock(InvoiceRepositoryInterface::class);
        $pricingService = $this->createMock(PricingService::class);

        // Scenario:
        // Reservation ended at 12:00.
        // User exits at 13:00 (1h overstay).
        // Penalty logic should apply.

        $userId = 'user-1';
        $parkingId = 10;
        $reservationId = 100;

        $entryTime = new DateTimeImmutable('2024-01-01 10:00:00');
        $reservationEnd = new DateTimeImmutable('2024-01-01 12:00:00');
        // We can't easily mock "now" inside the UseCase as it uses new DateTimeImmutable().
        // BUT, ExitParkingUseCase uses `new DateTimeImmutable()` for exit time.
        // This makes testing "exact time" hard without a Clock service.
        // However, for Unit Test purposes, if I mock Repo->save($session), I can inspect the $session object passed to it
        // and see if the exit time is > reservation end.
        // Or I can control the environment time via a helper if possible, or just accept that "now" > "2024-01-01".
        // Wait, "2024-01-01" is in the past. If the code runs "now", it will be > reservationEnd. So logic works.

        // Active Session
        $session = new ParkingSession(1, $userId, $parkingId, $reservationId, $entryTime, null, null, false);
        $sessionRepo->method('findActiveSessionByUserId')->willReturn($session);

        // Reservation
        // Calculated amount was 10.0
        $reservation = new Reservation($reservationId, $userId, $parkingId, $entryTime, $reservationEnd, 'confirmed', 10.0, null);
        $reservationRepo->method('findById')->willReturn($reservation);

        // Pricing Service
        // Should be called with actual duration.
        // Since "now" is way after "2024", duration is huge.
        // Let's assume calculatePrice returns 100.0
        $pricingService->method('calculatePrice')->willReturn(100.0);

        // Expect Session Save
        $sessionRepo->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (ParkingSession $s) {
                // Simulate Repository returning a new instance with ID
                return new ParkingSession(
                    999, // New ID
                    $s->getUserId(),
                    $s->getParkingId(),
                    $s->getReservationId(),
                    $s->getEntryDateTime(),
                    $s->getExitDateTime(),
                    $s->getFinalAmount(),
                    $s->isPenaltyApplied()
                );
            });

        // Expect Invoice Save
        $invoiceRepo->expects($this->once())->method('save');

        $useCase = new ExitParkingUseCase($sessionRepo, $reservationRepo, $invoiceRepo, $pricingService);
        $request = new ExitParkingRequest($userId, $parkingId);

        $response = $useCase->execute($request);

        $this->assertInstanceOf(ParkingSessionResponse::class, $response);

        // Verify Session has Penalty
        $this->assertTrue($session->isPenaltyApplied());

        // Verify Amount: 100 (Price) + 20 (Penalty) = 120.0
        $this->assertEquals(120.0, $session->getFinalAmount());
    }
}

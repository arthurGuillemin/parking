<?php

namespace Tests\Functional;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\Database\Database;
use App\Infrastructure\Persistence\ReservationRepository;
use App\Infrastructure\Persistence\ParkingRepository;
use App\Infrastructure\Persistence\ParkingSessionRepository;
use App\Infrastructure\Persistence\PricingRuleRepository;
use App\Application\UseCase\User\MakeReservation\MakeReservationUseCase;
use App\Application\UseCase\User\MakeReservation\MakeReservationRequest;
use App\Application\UseCase\User\EnterParking\EnterParkingUseCase;
use App\Application\UseCase\User\EnterParking\EnterParkingRequest;
use App\Application\UseCase\User\ExitParking\ExitParkingUseCase;
use App\Application\UseCase\User\ExitParking\ExitParkingRequest;
use App\Domain\Service\CheckAvailabilityService;
use App\Domain\Entity\Parking;
use App\Domain\Entity\PricingRule;
use PDO;

class ReservationFunctionalTest extends TestCase
{
    private PDO $pdo;
    private ReservationRepository $reservationRepository;
    private ParkingRepository $parkingRepository;
    private ParkingSessionRepository $parkingSessionRepository;
    private PricingRuleRepository $pricingRuleRepository;

    private MakeReservationUseCase $makeReservationUseCase;
    private EnterParkingUseCase $enterParkingUseCase;
    private ExitParkingUseCase $exitParkingUseCase;

    protected function setUp(): void
    {
        $_ENV['APP_ENV'] = 'test';
        $this->pdo = Database::getInstance();
        $this->createTables();

        $this->reservationRepository = new ReservationRepository($this->pdo);
        $this->parkingRepository = new ParkingRepository($this->pdo);
        $this->parkingSessionRepository = new ParkingSessionRepository($this->pdo);
        $this->pricingRuleRepository = new PricingRuleRepository($this->pdo);

        $checkAvailabilityService = new CheckAvailabilityService($this->reservationRepository);

        $this->makeReservationUseCase = new MakeReservationUseCase(
            $this->reservationRepository,
            $this->parkingRepository,
            $checkAvailabilityService,
            $this->pricingRuleRepository
        );

        $this->enterParkingUseCase = new EnterParkingUseCase(
            $this->reservationRepository,
            $this->parkingSessionRepository,
            $this->parkingRepository
        );

        $this->exitParkingUseCase = new ExitParkingUseCase(
            $this->parkingSessionRepository,
            $this->reservationRepository
        );
    }

    private function createTables(): void
    {
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS parkings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            owner_id TEXT,
            name TEXT,
            address TEXT,
            latitude REAL,
            longitude REAL,
            total_capacity INTEGER,
            open_24_7 INTEGER
        )');

        $this->pdo->exec('CREATE TABLE IF NOT EXISTS reservations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id TEXT,
            parking_id INTEGER,
            start_date TEXT,
            end_date TEXT,
            status TEXT,
            calculated_amount REAL,
            final_amount REAL
        )');
        // Ensure clean state
        $this->pdo->exec('DELETE FROM reservations');

        $this->pdo->exec('CREATE TABLE IF NOT EXISTS parking_sessions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id TEXT,
            parking_id INTEGER,
            reservation_id INTEGER,
            entry_date TEXT,
            exit_date TEXT,
            final_amount REAL,
            penalty_applied INTEGER
        )');
        $this->pdo->exec('DELETE FROM parking_sessions');

        $this->pdo->exec('CREATE TABLE IF NOT EXISTS pricing_rules (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            parking_id INTEGER,
            start_duration_minute INTEGER,
            end_duration_minute INTEGER,
            price_per_slice REAL,
            slice_in_minutes INTEGER,
            effective_date TEXT
        )');
        $this->pdo->exec('DELETE FROM pricing_rules');
    }

    public function testFullReservationCycle(): void
    {
        // 1. Create Parking
        $parking = new Parking(0, 'owner1', 'Test Parking', '123 St', 0, 0, 5, true);
        $savedParking = $this->parkingRepository->save($parking);
        $parkingId = $savedParking->getParkingId();

        // 2. Setup Pricing (10 per hour, 15m slice = 2.5)
        $rule = new PricingRule(0, $parkingId, 0, null, 2.5, 15, new \DateTimeImmutable());
        $this->pricingRuleRepository->save($rule);

        // 3. Make Reservation (2 hours)
        $start = new \DateTimeImmutable('2025-01-01 10:00:00');
        $end = new \DateTimeImmutable('2025-01-01 12:00:00');
        $resRequest = new MakeReservationRequest('user1', $parkingId, $start, $end);

        $resResponse = $this->makeReservationUseCase->execute($resRequest);

        $this->assertNotNull($resResponse->id);
        $this->assertEquals(20.0, $resResponse->amount); // 2h = 8 slices * 2.5 = 20

        // 4. Enter Parking (Simulate correct Time? Difficult as UseCase uses new DateTimeImmutable() now)
        // Since my UseCase executes `new DateTimeImmutable()`, I can't easily test time-sensitive logic unless I inject Clock or mock time.
        // Or I adjust method to accept time (unlikely for production code).
        // OR I rely on the fact that for Functional Test in SQLite I might not check "Active" strictly if I didn't enforce time check?
        // But `findActiveReservation` checks `start <= NOW <= end`.
        // If "NOW" is current system time, and reservation is 2025... it won't be active (assuming test runs in 2024/2026/Dec 2025).
        // The prompt context says "Current local time: 2025-12-09".
        // UseCase uses `new \DateTimeImmutable()`.

        // workaround: Make reservation for NOW.
        $now = new \DateTimeImmutable();
        $startNow = $now->modify('-1 minute');
        $endNow = $now->modify('+1 hour');

        $resRequestNow = new MakeReservationRequest('user2', $parkingId, $startNow, $endNow);
        $resNow = $this->makeReservationUseCase->execute($resRequestNow);

        // 5. Enter
        $enterRequest = new EnterParkingRequest('user2', $parkingId);
        $enterResponse = $this->enterParkingUseCase->execute($enterRequest);

        $this->assertNotNull($enterResponse->id);
        $this->assertEquals($resNow->id, $enterResponse->reservationId);

        // 6. Exit
        $exitRequest = new ExitParkingRequest('user2', $parkingId);
        $exitResponse = $this->exitParkingUseCase->execute($exitRequest);

        $this->assertNotNull($exitResponse->exitDateTime);
        $this->assertEquals($resNow->amount, $exitResponse->amount);
    }

    public function testAvailabilityCheck(): void
    {
        // Create parking with Capacity 1
        $parking = new Parking(0, 'owner2', 'Small Parking', '456 St', 0, 0, 1, true);
        $savedParking = $this->parkingRepository->save($parking);
        $parkingId = $savedParking->getParkingId();

        // User 1 books
        $start = new \DateTimeImmutable('2026-01-01 10:00');
        $end = new \DateTimeImmutable('2026-01-01 11:00');
        $this->makeReservationUseCase->execute(new MakeReservationRequest('user1', $parkingId, $start, $end));

        // User 2 tries to book same time
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Parking is full');
        $this->makeReservationUseCase->execute(new MakeReservationRequest('user2', $parkingId, $start, $end));
    }
}

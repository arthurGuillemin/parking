<?php

namespace Tests\Functional;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\Database\Database;
use App\Infrastructure\Persistence\Sql\SqlReservationRepository;
use App\Infrastructure\Persistence\Sql\SqlParkingRepository;
use App\Infrastructure\Persistence\Sql\SqlParkingSessionRepository;
use App\Infrastructure\Persistence\Sql\SqlPricingRuleRepository;
use App\Infrastructure\Persistence\Sql\SqlSubscriptionRepository;
use App\Infrastructure\Persistence\Sql\SqlInvoiceRepository;
use App\Application\UseCase\User\MakeReservation\MakeReservationUseCase;
use App\Application\UseCase\User\MakeReservation\MakeReservationRequest;
use App\Application\UseCase\User\EnterParking\EnterParkingUseCase;
use App\Application\UseCase\User\EnterParking\EnterParkingRequest;
use App\Application\UseCase\User\ExitParking\ExitParkingUseCase;
use App\Application\UseCase\User\ExitParking\ExitParkingRequest;
use App\Domain\Service\CheckAvailabilityService;
use App\Domain\Entity\Parking;
use App\Domain\Entity\PricingRule;

class ReservationFunctionalTest extends BaseFunctionalTest
{
    private MakeReservationUseCase $makeReservationUseCase;
    private EnterParkingUseCase $enterParkingUseCase;
    private ExitParkingUseCase $exitParkingUseCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->makeReservationUseCase = $this->container->get(MakeReservationUseCase::class);
        $this->enterParkingUseCase = $this->container->get(EnterParkingUseCase::class);
        $this->exitParkingUseCase = $this->container->get(ExitParkingUseCase::class);

        // Repositories are already in container, we might need them for setup
        // But we can use DB accessor from BaseFunctionalTest
    }

    public function testFullReservationCycle(): void
    {
        // 1. Create Parking (Owner table required first due to FK)
        $this->db->exec("INSERT INTO owners (id, email, password, first_name, last_name, creation_date) VALUES ('owner1', 'o@o.com', 'p', 'f', 'l', '2024-01-01')");

        $this->db->exec("INSERT INTO parkings (owner_id, name, address, latitude, longitude, total_capacity, open_24_7)
            VALUES ('owner1', 'Test Parking', '123 St', 0, 0, 5, 1)");
        $parkingId = $this->db->lastInsertId();

        // 2. Setup Pricing (10 per hour, 15m slice = 2.5)
        $this->db->exec("INSERT INTO pricing_rules (parking_id, start_duration_minute, end_duration_minute, price_per_slice, slice_in_minutes, effective_date)
            VALUES ($parkingId, 0, NULL, 2.50, 15, '2024-01-01')");

        // 3. Make Reservation (2 hours)
        // User required
        $this->db->exec("INSERT INTO users (id, email, password, first_name, last_name, creation_date) VALUES ('user1', 'u@u.com', 'p', 'f', 'l', '2024-01-01')");

        $start = new \DateTimeImmutable('2025-01-01 10:00:00');
        $end = new \DateTimeImmutable('2025-01-01 12:00:00');
        $resRequest = new MakeReservationRequest('user1', $parkingId, $start, $end);

        $resResponse = $this->makeReservationUseCase->execute($resRequest);

        $this->assertNotNull($resResponse->id);
        $this->assertEquals(20.0, $resResponse->amount); // 2h = 8 slices * 2.5 = 20

        // 4. Enter Parking
        // Need to simulate "current time" matching reservation or update reservation to NOW.
        // Let's create a new reservation for NOW.
        $this->db->exec("INSERT INTO users (id, email, password, first_name, last_name, creation_date) VALUES ('user2', 'u2@u.com', 'p', 'f', 'l', '2024-01-01')");

        $now = new \DateTimeImmutable();
        $startNow = $now->modify('+1 minute'); // Start in 1 min
        $endNow = $now->modify('+1 hour');

        $resRequestNow = new MakeReservationRequest('user2', $parkingId, $startNow, $endNow);
        $resNow = $this->makeReservationUseCase->execute($resRequestNow);

        // 5. Enter (Allowed 30 mins before)
        $enterRequest = new EnterParkingRequest('user2', $parkingId, $resNow->id);
        $enterResponse = $this->enterParkingUseCase->execute($enterRequest);

        $this->assertNotNull($enterResponse->id);
        $this->assertEquals($resNow->id, $enterResponse->reservationId);

        // 6. Exit
        $exitRequest = new ExitParkingRequest('user2', $parkingId);
        $exitResponse = $this->exitParkingUseCase->execute($exitRequest);

        $this->assertNotNull($exitResponse->exitDateTime);
        // Might be 0 if exit immediately? No, base price usually applied or recalc?
        // If exit before start time? It's OK.
        // If duration is seconds, price might be 1 slice?
        // Let's check status.
        $this->assertNotNull($exitResponse->amount);
    }

    public function testAvailabilityCheck(): void
    {
        $this->db->exec("INSERT INTO owners (id, email, password, first_name, last_name, creation_date) VALUES ('owner2', 'o2@o.com', 'p', 'f', 'l', '2024-01-01')");
        $this->db->exec("INSERT INTO parkings (owner_id, name, address, latitude, longitude, total_capacity, open_24_7)
            VALUES ('owner2', 'Small Parking', '456 St', 0, 0, 1, 1)");
        $parkingId = $this->db->lastInsertId();

        // User 1 books
        $this->db->exec("INSERT INTO users (id, email, password, first_name, last_name, creation_date) VALUES ('u1', 'u1@u.com', 'p', 'f', 'l', '2024-01-01')");

        $start = new \DateTimeImmutable('2026-01-01 10:00');
        $end = new \DateTimeImmutable('2026-01-01 11:00');
        $this->makeReservationUseCase->execute(new MakeReservationRequest('u1', $parkingId, $start, $end));

        // User 2 books
        $this->db->exec("INSERT INTO users (id, email, password, first_name, last_name, creation_date) VALUES ('u2', 'u2@u.com', 'p', 'f', 'l', '2024-01-01')");

        // User 2 tries to book same time
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Parking is full'); // Check CheckAvailabilityService message
        $this->makeReservationUseCase->execute(new MakeReservationRequest('u2', $parkingId, $start, $end));
    }
}

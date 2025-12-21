<?php

namespace Tests\Functional;

use App\Application\UseCase\User\Register\UserRegisterUseCase;
use App\Application\UseCase\User\Register\UserRegisterRequest;
use App\Application\UseCase\User\MakeReservation\MakeReservationUseCase;
use App\Application\UseCase\User\MakeReservation\MakeReservationRequest;
use App\Application\UseCase\User\ExitParking\ExitParkingUseCase;
use App\Application\UseCase\User\ExitParking\ExitParkingRequest;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Entity\Parking;
use App\Domain\Entity\PricingRule;
use App\Domain\Entity\ParkingSession;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

class UserScenarioTest extends BaseFunctionalTest
{
    public function testUserCanRegisterAndReserve()
    {
        // 1. Register User
        $registerUseCase = $this->container->get(UserRegisterUseCase::class);
        $req = new UserRegisterRequest('test@user.com', 'Pass1234', 'John', 'Doe');
        $user = $registerUseCase->execute($req);

        $this->assertNotNull($user->getUserId());

        // 2. Setup Parking Object manually in DB (Owner side not tested here)
        // Since we don't have OwnerUseCase in this test, insert manually via PDO or Repo
        $ownerId = Uuid::uuid4()->toString();
        $this->db->exec("INSERT INTO owners (id, email, password, first_name, last_name, creation_date) 
            VALUES ('$ownerId', 'owner@test.com', 'pass', 'Owner', 'One', '2024-01-01')");

        $this->db->exec("INSERT INTO parkings (owner_id, name, address, latitude, longitude, total_capacity, open_24_7)
            VALUES ('$ownerId', 'Test Parking', '123 St', 0.0, 0.0, 10, 1)");
        $parkingId = $this->db->lastInsertId();

        // 3. Add Pricing Rule (for pricing calculation)
        // 2€ per 15min
        $this->db->exec("INSERT INTO pricing_rules (parking_id, start_duration_minute, end_duration_minute, price_per_slice, slice_in_minutes, effective_date)
            VALUES ($parkingId, 0, 600, 2.0, 15, '2024-01-01 00:00:00')");

        // 4. Make Reservation
        $reserveUseCase = $this->container->get(MakeReservationUseCase::class);
        $start = new DateTimeImmutable('now + 1 hour');
        $end = new DateTimeImmutable('now + 2 hours'); // 1h duration => 4 slices * 2€ = 8€

        $resReq = new MakeReservationRequest($user->getUserId(), $parkingId, $start, $end);
        $response = $reserveUseCase->execute($resReq);

        $this->assertEquals(8.0, $response->amount);
        $this->assertEquals('pending', $response->status);
    }

    public function testOverstayPenalizesUser()
    {
        // Setup User & Parking
        $userId = Uuid::uuid4()->toString();
        $this->db->exec("INSERT INTO users (id, email, password, first_name, last_name, creation_date) 
            VALUES ('$userId', 'pf@user.com', 'pass', 'P', 'F', '2024-01-01')");

        $ownerId = Uuid::uuid4()->toString();
        $this->db->exec("INSERT INTO owners (id, email, password, first_name, last_name, creation_date) 
            VALUES ('$ownerId', 'owner2@test.com', 'pass', 'O', 'W', '2024-01-01')");

        $this->db->exec("INSERT INTO parkings (owner_id, name, address, latitude, longitude, total_capacity, open_24_7)
            VALUES ('$ownerId', 'P_Overstay', 'Add', 0, 0, 10, 1)");
        $parkingId = $this->db->lastInsertId();

        // Pricing: 1€ per 15min
        $this->db->exec("INSERT INTO pricing_rules (parking_id, start_duration_minute, end_duration_minute, price_per_slice, slice_in_minutes, effective_date)
            VALUES ($parkingId, 0, 1000, 1.0, 15, '2024-01-01 00:00:00')");

        // Reservation: 10:00 to 11:00 (1h) => 4 * 1€ = 4€
        $resId = 100;
        $this->db->exec("INSERT INTO reservations (id, user_id, parking_id, start_datetime, end_datetime, status, calculated_amount, final_amount)
            VALUES ($resId, '$userId', $parkingId, '2024-01-01 10:00:00', '2024-01-01 11:00:00', 'confirmed', 4.0, 4.0)");

        // Session: Entered 10:00. Active.
        $this->db->exec("INSERT INTO parking_sessions (user_id, parking_id, reservation_id, entry_time)
            VALUES ('$userId', $parkingId, $resId, '2024-01-01 10:00:00')");

        // Now simulate Exit at 12:00 (1h Overstay)
        // Since ExitParkingUseCase uses "new DateTimeImmutable()", we can't easily force "12:00" if real time is 2025.
        // BUT, real time is 2025 (Metadata says).
        // 2025 > 2024-01-01 11:00:00. So it IS an overstay.
        // Duration: 2024-01-01 10:00 to 2025-12-21... Huge duration.
        // Price will be Huge + 20€.
        // This confirms Penalty logic triggers.

        $exitUseCase = $this->container->get(ExitParkingUseCase::class);
        $req = new ExitParkingRequest($userId, $parkingId);

        $response = $exitUseCase->execute($req);

        // Verify Penalty Applied
        $this->assertTrue($response->penaltyApplied, 'Penalty should be applied');
        // Verify Amount > 4.0 (Base was 4.0)
        $this->assertGreaterThan(24.0, $response->amount); // 4 + 20 + massive usage
    }
}

<?php

namespace Tests\Functional;

use App\Application\UseCase\Owner\UpdatePricingRule\UpdatePricingRuleUseCase;
use App\Application\UseCase\Owner\UpdatePricingRule\UpdatePricingRuleRequest;
use App\Application\UseCase\Parking\CountAvailableParkingSpots\CountAvailableParkingSpotsUseCase;
use App\Application\DTO\Parking\CountAvailableParkingSpots\CountAvailableParkingSpotsRequest;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

class OwnerScenarioTest extends BaseFunctionalTest
{
    public function testOwnerCanAddPricingRule()
    {
        // Setup Owner & Parking
        $ownerId = Uuid::uuid4()->toString();
        $this->db->exec("INSERT INTO owners (id, email, password, first_name, last_name, creation_date) 
            VALUES ('$ownerId', 'pr@owner.com', 'pass', 'P', 'R', '2024-01-01')");

        $this->db->exec("INSERT INTO parkings (owner_id, name, address, latitude, longitude, total_capacity, open_24_7)
            VALUES ('$ownerId', 'Pricing Parking', 'Addr', 0, 0, 10, 1)");
        $parkingId = $this->db->lastInsertId();

        // Use Case
        $useCase = $this->container->get(UpdatePricingRuleUseCase::class);
        $request = new UpdatePricingRuleRequest(
            $parkingId,
            0,
            60, // 0-60 min
            5.0, // 5€ per slice
            15, // 15 min slice
            new DateTimeImmutable('2024-06-01')
        );

        $rule = $useCase->execute($request);

        $this->assertNotNull($rule->getPricingRuleId());
        $this->assertEquals(5.0, $rule->getPricePerSlice());

        // Verify DB
        $stmt = $this->db->query("SELECT COUNT(*) FROM pricing_rules WHERE parking_id = $parkingId");
        $this->assertEquals(1, $stmt->fetchColumn());
    }

    public function testOwnerCanCheckCapacity()
    {
        // Setup Parking (Capacity 10)
        $ownerId = Uuid::uuid4()->toString();
        $this->db->exec("INSERT INTO owners (id, email, password, first_name, last_name, creation_date) 
            VALUES ('$ownerId', 'cap@owner.com', 'pass', 'C', 'P', '2024-01-01')");

        $this->db->exec("INSERT INTO parkings (owner_id, name, address, latitude, longitude, total_capacity, open_24_7)
            VALUES ('$ownerId', 'Capacity Parking', 'Addr', 0, 0, 10, 1)");
        $parkingId = $this->db->lastInsertId();

        // Add 3 Active Sessions
        $userId = Uuid::uuid4()->toString();
        $this->db->exec("INSERT INTO users (id, email, password, first_name, last_name, creation_date) 
             VALUES ('$userId', 'u@u.com', 'p', 'f', 'l', '2024-01-01')");

        // 3 active reservations
        $this->db->exec("INSERT INTO reservations (id, user_id, parking_id, start_datetime, end_datetime, status, calculated_amount, final_amount) VALUES (101, '$userId', $parkingId, '2024-01-01 10:00:00', '2024-01-01 11:00:00', 'confirmed', 0, 0)");
        $this->db->exec("INSERT INTO reservations (id, user_id, parking_id, start_datetime, end_datetime, status, calculated_amount, final_amount) VALUES (102, '$userId', $parkingId, '2024-01-01 10:00:00', '2024-01-01 11:00:00', 'confirmed', 0, 0)");
        $this->db->exec("INSERT INTO reservations (id, user_id, parking_id, start_datetime, end_datetime, status, calculated_amount, final_amount) VALUES (103, '$userId', $parkingId, '2024-01-01 10:00:00', '2024-01-01 11:00:00', 'confirmed', 0, 0)");

        // Use Case: CountAvailableParkingSpotsUseCase
        // Wait, check Request signature. Usually just parkingId?
        // I will guess Request(parkingId) based on naming. 
        // Or directly use Service? The UseCase wraps it.

        $useCase = $this->container->get(CountAvailableParkingSpotsUseCase::class);
        $request = new CountAvailableParkingSpotsRequest($parkingId, new DateTimeImmutable('2024-01-01 10:30:00'));
        $available = $useCase->execute($request);

        // Capacity 10 - 3 Active = 7 Available
        $this->assertEquals(7, $available->availableSpots);
    }
}

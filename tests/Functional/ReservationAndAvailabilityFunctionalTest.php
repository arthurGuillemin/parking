<?php

namespace Tests\Functional;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\Database\Database;
use App\Infrastructure\Persistence\Sql\SqlParkingRepository;
use App\Infrastructure\Persistence\Sql\SqlReservationRepository;
use App\Infrastructure\Persistence\Sql\SqlSubscriptionRepository;
use App\Application\UseCase\Parking\CountAvailableParkingSpots\CountAvailableParkingSpotsUseCase;
use App\Application\DTO\Parking\CountAvailableParkingSpots\CountAvailableParkingSpotsRequest;
use App\Domain\Entity\Parking;
use App\Domain\Entity\Reservation;
use PDO;

/**
 * Test fonctionnel utilisateur : Réservation + Disponibilité
 */
class ReservationAndAvailabilityFunctionalTest extends TestCase
{
    private PDO $pdo;
    private SqlParkingRepository $parkingRepository;
    private SqlReservationRepository $reservationRepository;
    private SqlSubscriptionRepository $subscriptionRepository;
    private CountAvailableParkingSpotsUseCase $countAvailableSpotsUseCase;

    protected function setUp(): void
    {
        $_ENV['APP_ENV'] = 'test';
        $this->pdo = Database::getInstance();
        $this->createTables();
        $this->cleanTables();

        $this->parkingRepository = new SqlParkingRepository();
        $this->reservationRepository = new SqlReservationRepository();
        $this->subscriptionRepository = new SqlSubscriptionRepository();
        $this->countAvailableSpotsUseCase = new CountAvailableParkingSpotsUseCase(
            $this->parkingRepository,
            $this->reservationRepository,
            $this->subscriptionRepository
        );
    }

    private function cleanTables(): void
    {
        $this->pdo->exec('DELETE FROM reservations');
        $this->pdo->exec('DELETE FROM subscriptions');
        $this->pdo->exec('DELETE FROM parkings');
    }

    private function createTables(): void
    {
        $this->pdo->exec('
            CREATE TABLE IF NOT EXISTS parkings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                owner_id TEXT NOT NULL,
                name TEXT NOT NULL,
                address TEXT NOT NULL,
                latitude REAL NOT NULL,
                longitude REAL NOT NULL,
                total_capacity INTEGER NOT NULL,
                open_24_7 INTEGER DEFAULT 0 NOT NULL
            )
        ');

        $this->pdo->exec('
            CREATE TABLE IF NOT EXISTS reservations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id TEXT NOT NULL,
                parking_id INTEGER NOT NULL,
                start_date_time TEXT NOT NULL,
                end_date_time TEXT NOT NULL,
                status TEXT NOT NULL,
                calculated_amount REAL,
                final_amount REAL,
                FOREIGN KEY (parking_id) REFERENCES parkings(id)
            )
        ');

        $this->pdo->exec('
            CREATE TABLE IF NOT EXISTS subscriptions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id TEXT NOT NULL,
                parking_id INTEGER NOT NULL,
                type_id INTEGER,
                start_date TEXT NOT NULL,
                end_date TEXT,
                status TEXT NOT NULL,
                monthly_price REAL NOT NULL,
                FOREIGN KEY (parking_id) REFERENCES parkings(id)
            )
        ');
    }

    public function testParkingAvailabilityCalculation(): void
    {
        $parking = new Parking(
            1,
            'owner-1',
            'Parking Central',
            '123 Rue Test',
            48.8566,
            2.3522,
            10,
            true
        );
        $parking = $this->parkingRepository->save($parking);

        $request = new CountAvailableParkingSpotsRequest($parking->getParkingId(), new \DateTimeImmutable('2025-01-15 10:00:00'));
        $response = $this->countAvailableSpotsUseCase->execute($request);

        $this->assertEquals(10, $response->totalCapacity);
        $this->assertEquals(10, $response->availableSpots);
    }

    public function testReservationOccupiesSpotForEntireDuration(): void
    {
        $parking = new Parking(
            2,
            'owner-1',
            'Parking Test',
            '456 Rue Test',
            48.8566,
            2.3522,
            5,
            true
        );
        $parking = $this->parkingRepository->save($parking);

        $startDateTime = new \DateTimeImmutable('2025-01-15 10:00:00');
        $endDateTime = new \DateTimeImmutable('2025-01-15 12:00:00');

        $reservation = new Reservation(
            0,
            'user-1',
            $parking->getParkingId(),
            $startDateTime,
            $endDateTime,
            'confirmed',
            15.0,
            null
        );
        $this->reservationRepository->save($reservation);

        $duringReservation = new \DateTimeImmutable('2025-01-15 11:00:00');
        $request = new CountAvailableParkingSpotsRequest($parking->getParkingId(), $duringReservation);
        $response = $this->countAvailableSpotsUseCase->execute($request);

        $this->assertEquals(5, $response->totalCapacity);
        $this->assertLessThan(5, $response->availableSpots, 'Une place doit être occupée par la réservation');

        $beforeReservation = new \DateTimeImmutable('2025-01-15 09:00:00');
        $requestBefore = new CountAvailableParkingSpotsRequest($parking->getParkingId(), $beforeReservation);
        $responseBefore = $this->countAvailableSpotsUseCase->execute($requestBefore);
        $this->assertEquals(5, $responseBefore->availableSpots);

        $afterReservation = new \DateTimeImmutable('2025-01-15 13:00:00');
        $requestAfter = new CountAvailableParkingSpotsRequest($parking->getParkingId(), $afterReservation);
        $responseAfter = $this->countAvailableSpotsUseCase->execute($requestAfter);
        $this->assertEquals(5, $responseAfter->availableSpots);
    }

    public function testMultipleReservationsReduceAvailableSpots(): void
    {
        $parking = new Parking(
            3,
            'owner-1',
            'Parking Petit',
            '789 Rue Test',
            48.8566,
            2.3522,
            3,
            true
        );
        $parking = $this->parkingRepository->save($parking);

        $baseDateTime = new \DateTimeImmutable('2025-01-15 10:00:00');

        $reservation1 = new Reservation(
            0,
            'user-1',
            $parking->getParkingId(),
            $baseDateTime,
            $baseDateTime->modify('+2 hours'),
            'confirmed',
            20.0,
            null
        );
        $this->reservationRepository->save($reservation1);

        $reservation2 = new Reservation(
            0,
            'user-2',
            $parking->getParkingId(),
            $baseDateTime,
            $baseDateTime->modify('+2 hours'),
            'confirmed',
            20.0,
            null
        );
        $this->reservationRepository->save($reservation2);

        $checkTime = $baseDateTime->modify('+1 hour');
        $request = new CountAvailableParkingSpotsRequest($parking->getParkingId(), $checkTime);
        $response = $this->countAvailableSpotsUseCase->execute($request);

        $this->assertEquals(3, $response->totalCapacity);
        $this->assertLessThan(3, $response->availableSpots);
        $this->assertEquals(1, $response->availableSpots); // 3 - 2 = 1
    }

    public function testReservationRefusedWhenParkingFull(): void
    {
        $parking = new Parking(
            4,
            'owner-1',
            'Parking Complet',
            '999 Rue Test',
            48.8566,
            2.3522,
            2,
            true
        );
        $parking = $this->parkingRepository->save($parking);

        $baseDateTime = new \DateTimeImmutable('2025-01-15 10:00:00');

        $reservation1 = new Reservation(
            0,
            'user-1',
            $parking->getParkingId(),
            $baseDateTime,
            $baseDateTime->modify('+2 hours'),
            'confirmed',
            20.0,
            null
        );
        $this->reservationRepository->save($reservation1);

        $reservation2 = new Reservation(
            0,
            'user-2',
            $parking->getParkingId(),
            $baseDateTime,
            $baseDateTime->modify('+2 hours'),
            'confirmed',
            20.0,
            null
        );
        $this->reservationRepository->save($reservation2);

        $request = new CountAvailableParkingSpotsRequest($parking->getParkingId(), $baseDateTime->modify('+1 hour'));
        $response = $this->countAvailableSpotsUseCase->execute($request);

        $this->assertEquals(0, $response->availableSpots);
    }
}

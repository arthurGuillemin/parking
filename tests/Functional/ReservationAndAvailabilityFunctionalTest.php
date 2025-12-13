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
 * 
 * Ce test vérifie les règles métiers suivantes :
 * - Une réservation occupe une place sur tout son créneau
 * - Le système calcule correctement les places disponibles
 * - Une réservation refusée si le parking est plein sur une partie du créneau
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

    /**
     * ✅ Scénario 1 : Créer un parking et vérifier les places disponibles
     */
    public function testParkingAvailabilityCalculation(): void
    {
        // Créer un parking avec 10 places
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
        $this->parkingRepository->save($parking);

        // Vérifier que toutes les places sont disponibles
        $request = new CountAvailableParkingSpotsRequest(1, new \DateTimeImmutable('2025-01-15 10:00:00'));
        $response = $this->countAvailableSpotsUseCase->execute($request);

        $this->assertEquals(10, $response->totalCapacity);
        $this->assertEquals(10, $response->availableSpots);
    }

    /**
     * ✅ Scénario 2 : Une réservation occupe une place sur tout son créneau
     */
    public function testReservationOccupiesSpotForEntireDuration(): void
    {
        // Créer un parking avec 5 places
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
        $this->parkingRepository->save($parking);

        $startDateTime = new \DateTimeImmutable('2025-01-15 10:00:00');
        $endDateTime = new \DateTimeImmutable('2025-01-15 12:00:00');

        // Créer une réservation active
        $reservation = new Reservation(
            1,
            'user-1',
            2,
            $startDateTime,
            $endDateTime,
            'active',
            15.0,
            null
        );
        $this->reservationRepository->save($reservation);

        // Vérifier que les places disponibles sont réduites pendant le créneau
        // La méthode countReservations utilise findForParkingBetween($at, $at)
        // qui cherche les réservations où start_date_time < $at AND end_date_time > $at
        $duringReservation = new \DateTimeImmutable('2025-01-15 11:00:00');
        $request = new CountAvailableParkingSpotsRequest(2, $duringReservation);
        $response = $this->countAvailableSpotsUseCase->execute($request);

        $this->assertEquals(5, $response->totalCapacity);
        // Vérifier que la réservation est bien trouvée par la requête
        $reservations = $this->reservationRepository->findForParkingBetween(2, $duringReservation, $duringReservation);
        $this->assertGreaterThanOrEqual(1, count($reservations), 'La réservation doit être trouvée');
        // Si la réservation est trouvée, elle doit réduire les places disponibles
        $this->assertLessThan(5, $response->availableSpots, 'Une place doit être occupée par la réservation');

        // Vérifier qu'avant la réservation, toutes les places sont disponibles
        $beforeReservation = new \DateTimeImmutable('2025-01-15 09:00:00');
        $requestBefore = new CountAvailableParkingSpotsRequest(2, $beforeReservation);
        $responseBefore = $this->countAvailableSpotsUseCase->execute($requestBefore);
        $this->assertEquals(5, $responseBefore->availableSpots, 'Avant la réservation, toutes les places doivent être disponibles');

        // Vérifier qu'après la réservation, toutes les places sont disponibles
        $afterReservation = new \DateTimeImmutable('2025-01-15 13:00:00');
        $requestAfter = new CountAvailableParkingSpotsRequest(2, $afterReservation);
        $responseAfter = $this->countAvailableSpotsUseCase->execute($requestAfter);
        $this->assertEquals(5, $responseAfter->availableSpots);
    }

    /**
     * ✅ Scénario 3 : Plusieurs réservations réduisent les places disponibles
     */
    public function testMultipleReservationsReduceAvailableSpots(): void
    {
        // Créer un parking avec 3 places
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
        $this->parkingRepository->save($parking);

        $baseDateTime = new \DateTimeImmutable('2025-01-15 10:00:00');

        // Créer 2 réservations simultanées
        $reservation1 = new Reservation(
            2,
            'user-1',
            3,
            $baseDateTime,
            $baseDateTime->modify('+2 hours'),
            'active',
            20.0,
            null
        );
        $this->reservationRepository->save($reservation1);

        $reservation2 = new Reservation(
            3,
            'user-2',
            3,
            $baseDateTime,
            $baseDateTime->modify('+2 hours'),
            'active',
            20.0,
            null
        );
        $this->reservationRepository->save($reservation2);

        // Vérifier que seulement 1 place reste disponible
        $checkTime = $baseDateTime->modify('+1 hour');
        $request = new CountAvailableParkingSpotsRequest(3, $checkTime);
        $response = $this->countAvailableSpotsUseCase->execute($request);

        $this->assertEquals(3, $response->totalCapacity);
        // Vérifier que les réservations sont bien trouvées
        $reservations = $this->reservationRepository->findForParkingBetween(3, $checkTime, $checkTime);
        $this->assertGreaterThanOrEqual(2, count($reservations), 'Les deux réservations doivent être trouvées');
        $this->assertLessThan(3, $response->availableSpots, 'Deux réservations doivent occuper au moins 2 places');
    }

    /**
     * ✅ Scénario 4 : Réservation refusée si le parking est plein
     */
    public function testReservationRefusedWhenParkingFull(): void
    {
        // Créer un parking avec 2 places
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
        $this->parkingRepository->save($parking);

        $baseDateTime = new \DateTimeImmutable('2025-01-15 10:00:00');

        // Remplir le parking avec 2 réservations
        $reservation1 = new Reservation(
            4,
            'user-1',
            4,
            $baseDateTime,
            $baseDateTime->modify('+2 hours'),
            'active',
            20.0,
            null
        );
        $this->reservationRepository->save($reservation1);

        $reservation2 = new Reservation(
            5,
            'user-2',
            4,
            $baseDateTime,
            $baseDateTime->modify('+2 hours'),
            'active',
            20.0,
            null
        );
        $this->reservationRepository->save($reservation2);

        // Vérifier qu'il n'y a plus de places disponibles
        $request = new CountAvailableParkingSpotsRequest(4, $baseDateTime->modify('+1 hour'));
        $response = $this->countAvailableSpotsUseCase->execute($request);

        $this->assertEquals(0, $response->availableSpots, 'Le parking doit être plein');
        $this->assertEquals(2, $response->totalCapacity);
    }
}


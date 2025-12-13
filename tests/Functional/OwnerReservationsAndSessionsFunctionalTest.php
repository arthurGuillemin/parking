<?php

namespace Tests\Functional;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\Database\Database;
use App\Infrastructure\Persistence\Sql\SqlParkingRepository;
use App\Infrastructure\Persistence\Sql\SqlReservationRepository;
use App\Infrastructure\Persistence\Sql\SqlParkingSessionRepository;
use App\Application\UseCase\Owner\ListReservations\ListReservationsUseCase;
use App\Application\UseCase\Owner\ListReservations\ListReservationsRequest;
use App\Application\UseCase\Owner\ListParkingSessions\ListParkingSessionsUseCase;
use App\Application\UseCase\Owner\ListParkingSessions\ListParkingSessionsRequest;
use App\Domain\Entity\Parking;
use App\Domain\Entity\Reservation;
use App\Domain\Entity\ParkingSession;
use PDO;

/**
 * Test fonctionnel propriétaire : Réservations + Stationnements
 * 
 * Ce test vérifie les règles métiers suivantes :
 * - Un propriétaire peut voir toutes les réservations de son parking
 * - Un propriétaire peut voir tous les stationnements de son parking
 * - Les filtres par date fonctionnent correctement
 */
class OwnerReservationsAndSessionsFunctionalTest extends TestCase
{
    private PDO $pdo;
    private SqlParkingRepository $parkingRepository;
    private SqlReservationRepository $reservationRepository;
    private SqlParkingSessionRepository $parkingSessionRepository;
    private ListReservationsUseCase $listReservationsUseCase;
    private ListParkingSessionsUseCase $listParkingSessionsUseCase;

    protected function setUp(): void
    {
        $_ENV['APP_ENV'] = 'test';
        $this->pdo = Database::getInstance();
        $this->createTables();

        $this->parkingRepository = new SqlParkingRepository();
        $this->reservationRepository = new SqlReservationRepository();
        $this->parkingSessionRepository = new SqlParkingSessionRepository();
        $this->listReservationsUseCase = new ListReservationsUseCase($this->reservationRepository);
        $this->listParkingSessionsUseCase = new ListParkingSessionsUseCase($this->parkingSessionRepository);
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
            CREATE TABLE IF NOT EXISTS parking_sessions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id TEXT NOT NULL,
                parking_id INTEGER NOT NULL,
                reservation_id INTEGER,
                entry_date_time TEXT NOT NULL,
                exit_date_time TEXT,
                final_amount REAL,
                penalty_applied INTEGER DEFAULT 0 NOT NULL,
                FOREIGN KEY (parking_id) REFERENCES parkings(id),
                FOREIGN KEY (reservation_id) REFERENCES reservations(id)
            )
        ');
    }

    /**
     * ✅ Scénario 1 : Lister toutes les réservations d'un parking
     */
    public function testListAllReservationsForParking(): void
    {
        // Créer un parking
        $parking = new Parking(
            1,
            'owner-1',
            'Parking Test',
            '123 Rue Test',
            48.8566,
            2.3522,
            10,
            true
        );
        $this->parkingRepository->save($parking);

        // Créer plusieurs réservations
        $reservation1 = new Reservation(
            1,
            'user-1',
            1,
            new \DateTimeImmutable('2025-01-15 10:00:00'),
            new \DateTimeImmutable('2025-01-15 12:00:00'),
            'active',
            15.0,
            null
        );
        $this->reservationRepository->save($reservation1);

        $reservation2 = new Reservation(
            2,
            'user-2',
            1,
            new \DateTimeImmutable('2025-01-16 14:00:00'),
            new \DateTimeImmutable('2025-01-16 16:00:00'),
            'completed',
            20.0,
            20.0
        );
        $this->reservationRepository->save($reservation2);

        // Lister les réservations
        $request = new ListReservationsRequest(1);
        $reservations = $this->listReservationsUseCase->execute($request);

        $this->assertCount(2, $reservations, 'Le parking doit avoir 2 réservations');
        $this->assertEquals(1, $reservations[0]->getParkingId());
        $this->assertEquals(1, $reservations[1]->getParkingId());
    }

    /**
     * ✅ Scénario 2 : Filtrer les réservations par date
     */
    public function testFilterReservationsByDateRange(): void
    {
        // Créer un parking
        $parking = new Parking(
            2,
            'owner-1',
            'Parking Filtre',
            '456 Rue Test',
            48.8566,
            2.3522,
            10,
            true
        );
        $this->parkingRepository->save($parking);

        // Créer des réservations à différentes dates
        $reservation1 = new Reservation(
            3,
            'user-1',
            2,
            new \DateTimeImmutable('2025-01-15 10:00:00'),
            new \DateTimeImmutable('2025-01-15 12:00:00'),
            'active',
            15.0,
            null
        );
        $this->reservationRepository->save($reservation1);

        $reservation2 = new Reservation(
            4,
            'user-2',
            2,
            new \DateTimeImmutable('2025-02-15 14:00:00'),
            new \DateTimeImmutable('2025-02-15 16:00:00'),
            'active',
            20.0,
            null
        );
        $this->reservationRepository->save($reservation2);

        // Filtrer pour janvier uniquement
        $start = new \DateTimeImmutable('2025-01-01 00:00:00');
        $end = new \DateTimeImmutable('2025-01-31 23:59:59');
        $request = new ListReservationsRequest(2, $start, $end);
        $reservations = $this->listReservationsUseCase->execute($request);

        $this->assertCount(1, $reservations, 'Seule la réservation de janvier doit être retournée');
        $this->assertEquals(3, $reservations[0]->getReservationId());
    }

    /**
     * ✅ Scénario 3 : Lister tous les stationnements d'un parking
     */
    public function testListAllParkingSessionsForParking(): void
    {
        // Créer un parking
        $parking = new Parking(
            3,
            'owner-1',
            'Parking Sessions',
            '789 Rue Test',
            48.8566,
            2.3522,
            10,
            true
        );
        $this->parkingRepository->save($parking);

        // Créer plusieurs stationnements
        $session1 = new ParkingSession(
            1,
            'user-1',
            3,
            1, // reservation_id
            new \DateTimeImmutable('2025-01-15 10:00:00'),
            new \DateTimeImmutable('2025-01-15 12:00:00'),
            15.0,
            false
        );
        $this->parkingSessionRepository->save($session1);

        $session2 = new ParkingSession(
            2,
            'user-2',
            3,
            null, // pas de réservation
            new \DateTimeImmutable('2025-01-16 14:00:00'),
            null, // en cours
            null,
            false
        );
        $this->parkingSessionRepository->save($session2);

        // Lister les stationnements
        $request = new ListParkingSessionsRequest(3);
        $sessions = $this->listParkingSessionsUseCase->execute($request);

        $this->assertCount(2, $sessions, 'Le parking doit avoir 2 stationnements');
        $this->assertEquals(3, $sessions[0]->getParkingId());
        $this->assertEquals(3, $sessions[1]->getParkingId());
    }

    /**
     * ✅ Scénario 4 : Vérifier que seuls les stationnements du parking concerné sont retournés
     */
    public function testParkingSessionsAreFilteredByParkingId(): void
    {
        // Créer deux parkings
        $parking1 = new Parking(
            4,
            'owner-1',
            'Parking 1',
            '111 Rue Test',
            48.8566,
            2.3522,
            10,
            true
        );
        $this->parkingRepository->save($parking1);

        $parking2 = new Parking(
            5,
            'owner-1',
            'Parking 2',
            '222 Rue Test',
            48.8566,
            2.3522,
            10,
            true
        );
        $this->parkingRepository->save($parking2);

        // Créer des stationnements pour chaque parking
        $session1 = new ParkingSession(
            3,
            'user-1',
            4, // parking 1
            1,
            new \DateTimeImmutable('2025-01-15 10:00:00'),
            new \DateTimeImmutable('2025-01-15 12:00:00'),
            15.0,
            false
        );
        $this->parkingSessionRepository->save($session1);

        $session2 = new ParkingSession(
            4,
            'user-2',
            5, // parking 2
            null,
            new \DateTimeImmutable('2025-01-16 14:00:00'),
            null,
            null,
            false
        );
        $this->parkingSessionRepository->save($session2);

        // Lister les stationnements du parking 1 uniquement
        $request = new ListParkingSessionsRequest(4);
        $sessions = $this->listParkingSessionsUseCase->execute($request);

        $this->assertCount(1, $sessions, 'Seul le stationnement du parking 1 doit être retourné');
        $this->assertEquals(4, $sessions[0]->getParkingId());
    }
}


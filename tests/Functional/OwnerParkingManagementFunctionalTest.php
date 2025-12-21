<?php

namespace Tests\Functional;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\Database\Database;
use App\Infrastructure\Persistence\Sql\SqlParkingRepository;
use App\Infrastructure\Persistence\Sql\SqlInvoiceRepository;
use App\Infrastructure\Persistence\Sql\SqlSubscriptionRepository;
use App\Application\UseCase\Owner\GetMonthlyRevenue\GetMonthlyRevenueUseCase;
use App\Application\UseCase\Owner\GetMonthlyRevenue\GetMonthlyRevenueRequest;
use App\Domain\Entity\Parking;
use App\Domain\Entity\Invoice;
use App\Domain\Entity\Subscription;
use App\Infrastructure\Persistence\Sql\SqlReservationRepository;
use PDO;

/**
 * Test fonctionnel propriétaire : Gestion parking + Revenus mensuels
 * 
 * Ce test vérifie les règles métiers suivantes :
 * - Calcul du chiffre d'affaires mensuel (réservations + abonnements)
 * - Les factures sont correctement comptabilisées
 * - Les abonnements sont inclus dans le calcul des revenus
 */
class OwnerParkingManagementFunctionalTest extends TestCase
{
    private PDO $pdo;
    private SqlParkingRepository $parkingRepository;
    private SqlInvoiceRepository $invoiceRepository;
    private SqlSubscriptionRepository $subscriptionRepository;
    private SqlReservationRepository $reservationRepository;
    private GetMonthlyRevenueUseCase $getMonthlyRevenueUseCase;

    protected function setUp(): void
    {
        $_ENV['APP_ENV'] = 'test';
        $this->pdo = Database::getInstance();
        $this->createTables();

        $this->parkingRepository = new SqlParkingRepository();
        $this->invoiceRepository = new SqlInvoiceRepository();
        $this->subscriptionRepository = new SqlSubscriptionRepository();
        $this->reservationRepository = new SqlReservationRepository();
        $this->getMonthlyRevenueUseCase = new GetMonthlyRevenueUseCase(
            $this->invoiceRepository,
            $this->subscriptionRepository
        );
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
            CREATE TABLE IF NOT EXISTS invoices (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                reservation_id INTEGER,
                session_id INTEGER,
                issue_date TEXT NOT NULL,
                amount_ht REAL NOT NULL,
                amount_ttc REAL NOT NULL,
                details_json TEXT,
                invoice_type TEXT NOT NULL,
                FOREIGN KEY (reservation_id) REFERENCES reservations(id)
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
     * ✅ Scénario 1 : Calcul des revenus mensuels avec factures uniquement
     */
    public function testMonthlyRevenueWithInvoicesOnly(): void
    {
        // Créer un parking
        $parking = new Parking(
            1,
            'owner-1',
            'Parking Revenus',
            '123 Rue Test',
            48.8566,
            2.3522,
            10,
            true
        );
        $this->parkingRepository->save($parking);

        // Créer des réservations pour lier les factures
        $reservation1 = new \App\Domain\Entity\Reservation(
            1,
            'user-1',
            1,
            new \DateTimeImmutable('2025-01-15 10:00:00'),
            new \DateTimeImmutable('2025-01-15 12:00:00'),
            'completed',
            15.0,
            18.0
        );
        $this->pdo->exec('
            INSERT INTO reservations (id, user_id, parking_id, start_date_time, end_date_time, status, calculated_amount, final_amount)
            VALUES (1, "user-1", 1, "2025-01-15 10:00:00", "2025-01-15 12:00:00", "completed", 15.0, 18.0)
        ');

        $this->pdo->exec('
            INSERT INTO reservations (id, user_id, parking_id, start_date_time, end_date_time, status, calculated_amount, final_amount)
            VALUES (2, "user-2", 1, "2025-01-20 14:00:00", "2025-01-20 16:00:00", "completed", 25.0, 30.0)
        ');

        // Créer des factures pour janvier 2025
        $invoice1 = new Invoice(
            1,
            1, // reservation_id
            null, // session_id
            new \DateTimeImmutable('2025-01-15 10:00:00'),
            15.0, // HT
            18.0, // TTC
            null,
            'reservation'
        );
        $this->invoiceRepository->save($invoice1);

        $invoice2 = new Invoice(
            2,
            2, // reservation_id
            null,
            new \DateTimeImmutable('2025-01-20 14:00:00'),
            25.0, // HT
            30.0, // TTC
            null,
            'reservation'
        );
        $this->invoiceRepository->save($invoice2);

        // Calculer les revenus de janvier 2025
        $request = new GetMonthlyRevenueRequest(1, 2025, 1);
        $revenue = $this->getMonthlyRevenueUseCase->execute($request);

        $this->assertEquals(48.0, $revenue['total'], 'Les revenus doivent être la somme des factures TTC (18 + 30)');
    }

    /**
     * ✅ Scénario 2 : Calcul des revenus mensuels avec abonnements uniquement
     */
    public function testMonthlyRevenueWithSubscriptionsOnly(): void
    {
        // Créer un parking
        $parking = new Parking(
            2,
            'owner-1',
            'Parking Abonnements',
            '456 Rue Test',
            48.8566,
            2.3522,
            10,
            true
        );
        $this->parkingRepository->save($parking);

        // Créer des abonnements actifs en janvier 2025
        $subscription1 = new Subscription(
            1,
            'user-1',
            2,
            1, // type_id
            new \DateTimeImmutable('2025-01-01'),
            new \DateTimeImmutable('2025-02-01'),
            'active',
            49.99
        );
        $this->subscriptionRepository->save($subscription1);

        $subscription2 = new Subscription(
            2,
            'user-2',
            2,
            1, // type_id
            new \DateTimeImmutable('2025-01-01'),
            new \DateTimeImmutable('2025-02-01'),
            'active',
            79.99
        );
        $this->subscriptionRepository->save($subscription2);

        // Calculer les revenus de janvier 2025
        $request = new GetMonthlyRevenueRequest(2, 2025, 1);
        $revenue = $this->getMonthlyRevenueUseCase->execute($request);

        $this->assertEquals(129.98, $revenue['total'], 'Les revenus doivent être la somme des prix mensuels (49.99 + 79.99)');
    }

    /**
     * ✅ Scénario 3 : Calcul des revenus mensuels avec factures ET abonnements
     */
    public function testMonthlyRevenueWithInvoicesAndSubscriptions(): void
    {
        // Créer un parking
        $parking = new Parking(
            3,
            'owner-1',
            'Parking Complet',
            '789 Rue Test',
            48.8566,
            2.3522,
            10,
            true
        );
        $this->parkingRepository->save($parking);

        // Créer une réservation pour lier la facture
        $this->pdo->exec('
            INSERT INTO reservations (id, user_id, parking_id, start_date_time, end_date_time, status, calculated_amount, final_amount)
            VALUES (3, "user-3", 3, "2025-01-10 10:00:00", "2025-01-10 12:00:00", "completed", 20.0, 24.0)
        ');

        // Créer des factures pour janvier 2025
        $invoice = new Invoice(
            3,
            3, // reservation_id
            null,
            new \DateTimeImmutable('2025-01-10 10:00:00'),
            20.0, // HT
            24.0, // TTC
            null,
            'reservation'
        );
        $this->invoiceRepository->save($invoice);

        // Créer un abonnement actif en janvier 2025
        $subscription = new Subscription(
            3,
            'user-3',
            3,
            1, // type_id
            new \DateTimeImmutable('2025-01-01'),
            new \DateTimeImmutable('2025-02-01'),
            'active',
            59.99
        );
        $this->subscriptionRepository->save($subscription);

        // Calculer les revenus de janvier 2025
        $request = new GetMonthlyRevenueRequest(3, 2025, 1);
        $revenue = $this->getMonthlyRevenueUseCase->execute($request);

        $this->assertEqualsWithDelta(83.99, $revenue['total'], 0.01, 'Les revenus doivent être la somme des factures TTC + prix mensuel (24 + 59.99)');
    }

    /**
     * ✅ Scénario 4 : Les factures d'autres mois ne sont pas comptabilisées
     */
    public function testMonthlyRevenueExcludesOtherMonths(): void
    {
        // Créer un parking
        $parking = new Parking(
            4,
            'owner-1',
            'Parking Filtre',
            '999 Rue Test',
            48.8566,
            2.3522,
            10,
            true
        );
        $this->parkingRepository->save($parking);

        // Créer des réservations pour lier les factures
        $this->pdo->exec('
            INSERT INTO reservations (id, user_id, parking_id, start_date_time, end_date_time, status, calculated_amount, final_amount)
            VALUES (4, "user-4", 4, "2025-01-15 10:00:00", "2025-01-15 12:00:00", "completed", 10.0, 12.0)
        ');

        $this->pdo->exec('
            INSERT INTO reservations (id, user_id, parking_id, start_date_time, end_date_time, status, calculated_amount, final_amount)
            VALUES (5, "user-5", 4, "2025-02-15 10:00:00", "2025-02-15 12:00:00", "completed", 20.0, 24.0)
        ');

        // Créer une facture pour janvier 2025
        $invoiceJan = new Invoice(
            4,
            4, // reservation_id
            null,
            new \DateTimeImmutable('2025-01-15 10:00:00'),
            10.0, // HT
            12.0, // TTC
            null,
            'reservation'
        );
        $this->invoiceRepository->save($invoiceJan);

        // Créer une facture pour février 2025 (ne doit pas être comptabilisée)
        $invoiceFeb = new Invoice(
            5,
            5, // reservation_id
            null,
            new \DateTimeImmutable('2025-02-15 10:00:00'),
            20.0, // HT
            24.0, // TTC
            null,
            'reservation'
        );
        $this->invoiceRepository->save($invoiceFeb);

        // Calculer les revenus de janvier 2025 uniquement
        $request = new GetMonthlyRevenueRequest(4, 2025, 1);
        $revenue = $this->getMonthlyRevenueUseCase->execute($request);

        $this->assertEquals(12.0, $revenue['total'], 'Seules les factures de janvier doivent être comptabilisées');
    }
}


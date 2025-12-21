<?php

namespace Tests\Functional;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\Database\Database;
use App\Infrastructure\Container\ServiceContainer;
use PDO;

abstract class BaseFunctionalTest extends TestCase
{
    protected ServiceContainer $container;
    protected PDO $db;

    protected function setUp(): void
    {
        // Ensure we are in test environment (should be set by phpunit.xml)
        // Reset Database Singleton if possible?
        // Database::getInstance() checks self::$instance.
        // If it was already created by another test, it persists. 
        // For 'sqlite::memory:', persistence means data remains if connection remains.
        // But if Singleton holds the connection, clean slate is needed.
        // We might need to Drop tables or re-create DB.

        // Since Database doesn't have a reset method, we'll try to execute schema.sql on the existing connection.
        // Ideally we should close connection, but we can't easily.
        // SQLite memory is per-connection.

        $this->db = Database::getInstance();
        $this->container = new ServiceContainer(); // New container for each test

        $this->initSchema();
    }

    private function initSchema()
    {
        // simplistic reset: drop all known tables (reverse order of dependency)
        $tables = [
            'invoices',
            'parking_sessions',
            'reservations',
            'subscription_slots',
            'subscriptions',
            'subscription_types',
            'opening_hours',
            'pricing_rules',
            'parkings',
            'owners',
            'users'
        ];

        foreach ($tables as $table) {
            $this->db->exec("DROP TABLE IF EXISTS $table");
        }

        $sql = file_get_contents(__DIR__ . '/../../config/schema.sql');

        // SQLite Compatibility Fixes
        $sql = str_replace('INT PRIMARY KEY AUTO_INCREMENT', 'INTEGER PRIMARY KEY AUTOINCREMENT', $sql);
        $sql = str_replace('AUTO_INCREMENT', 'AUTOINCREMENT', $sql); // Catch others
        $sql = str_replace('DECIMAL(10, 2)', 'REAL', $sql); // SQLite uses REAL or TEXT
        $sql = str_replace('DECIMAL(10, 8)', 'REAL', $sql);
        $sql = str_replace('DECIMAL(11, 8)', 'REAL', $sql);

        // Execute line by line or split by statement
        // Schema uses ; at end of statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        foreach ($statements as $stmt) {
            if (empty($stmt))
                continue;
            try {
                $this->db->exec($stmt);
            } catch (\PDOException $e) {
                // Ignore "already exists" if any, or log
                // But for first run it should be clean
                throw new \RuntimeException("SQL Error: " . $e->getMessage() . " in query: $stmt");
            }
        }
    }
}

<?php

namespace App\Infrastructure\Database;

use PDO;
use PDOException;

require __DIR__ . '/../../../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__, 3));
$dotenv->load();

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}


    public static function getInstance(): PDO
    {
        if (self::$instance === null) {

            // opur les tests sqlite
            if (!empty($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'test') {
                self::$instance = new PDO('sqlite::memory:');
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return self::$instance;
            }

            // en prod supabase
            $host = $_ENV['DB_HOST'] ?? null;
            $db   = $_ENV['DB_NAME'] ?? null;
            $user = $_ENV['DB_USER'] ?? null;
            $pass = $_ENV['DB_PASSWORD'] ?? null;
            $port = $_ENV['DB_PORT'] ?? 5432;

            if (!$host || !$db || !$user) {
                throw new \RuntimeException("Missing required database environment variables");
            }

            $dsn = "pgsql:host=$host;port=$port;dbname=$db";

            try {
                self::$instance = new PDO(
                    $dsn,
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );
            } catch (PDOException $e) {
                throw new \RuntimeException("db connecton failed: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}

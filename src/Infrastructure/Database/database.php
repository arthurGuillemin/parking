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

            $host = $_ENV['DB_HOST'];
            $db   = $_ENV['DB_NAME'];
            $user = $_ENV['DB_USER'];
            $pass = $_ENV['DB_PASSWORD'];
            $port = $_ENV['DB_PORT'];

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

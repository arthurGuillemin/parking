<?php
require __DIR__ . '/../vendor/autoload.php';
use App\Infrastructure\Database\Database;

try {
    $pdo = Database::getInstance();
    echo "Adding price column to subscription_types...\n";

    // Check if column exists
    // Postgres specific
    $stmt = $pdo->query("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_name='subscription_types' AND column_name='monthly_price';
    ");

    if ($stmt->fetch()) {
        echo "Column monthly_price already exists.\n";
    } else {
        $pdo->exec("ALTER TABLE subscription_types ADD COLUMN monthly_price DECIMAL(10, 2) NOT NULL DEFAULT 50.00");
        echo "Column monthly_price added.\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

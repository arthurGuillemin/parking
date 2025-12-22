<?php

require __DIR__ . '/vendor/autoload.php';

use App\Infrastructure\Database\Database;

try {
    $pdo = Database::getInstance();
    $stmt = $pdo->prepare("
        SELECT column_name, data_type 
        FROM information_schema.columns 
        WHERE table_name = 'subscription_slots'
    ");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Columns in 'subscription_slots':\n";
    foreach ($columns as $col) {
        echo "- " . $col['column_name'] . " (" . $col['data_type'] . ")\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

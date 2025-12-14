<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Database\Database;

try {
    $pdo = Database::getInstance();

    echo "Connected. searching for reservation_status enum...\n";

    $stmt = $pdo->query("
        SELECT t.typname, e.enumlabel
        FROM pg_type t
        JOIN pg_enum e ON t.oid = e.enumtypid
        WHERE t.typname = 'reservation_status'
    ");

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
        echo "No enum 'reservation_status' found. Listing all enums:\n";
        $stmtAll = $pdo->query("
            SELECT t.typname, e.enumlabel
            FROM pg_type t
            JOIN pg_enum e ON t.oid = e.enumtypid
        ");
        print_r($stmtAll->fetchAll(PDO::FETCH_ASSOC));
    } else {
        foreach ($rows as $row) {
            echo " - " . $row['enumlabel'] . "\n";
        }
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Database\Database;

$idToCheck = '74cc6d79-6b6d-434f-8daa-de2bf87c8406'; // The ID from the error

try {
    $pdo = Database::getInstance();
    echo "Checking ID: $idToCheck\n";

    // Check Users
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $idToCheck]);
    $user = $stmt->fetch();

    if ($user) {
        echo "Found in USERS table: " . $user['email'] . "\n";
    } else {
        echo "Not found in USERS table.\n";
    }

    // Check Owners
    $stmt = $pdo->prepare("SELECT * FROM owners WHERE id = :id");
    $stmt->execute(['id' => $idToCheck]);
    $owner = $stmt->fetch();

    if ($owner) {
        echo "Found in OWNERS table: " . $owner['email'] . "\n";
    } else {
        echo "Not found in OWNERS table.\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

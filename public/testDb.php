<?php
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
}
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$id = 1;
try {
    $pdo = new PDO(
        "pgsql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_NAME']}",
        $_ENV['DB_USER'],
        $_ENV['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die(json_encode(['error' => 'Connexion échouée : ' . $e->getMessage()]));
}

$id = 1;



try {
    $stmt = $pdo->prepare("SELECT * FROM parking WHERE id_parking = :id");
    $stmt->execute(['id' => $id]);
    $parking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($parking) {
        echo json_encode($parking, JSON_PRETTY_PRINT);
    } else {
        echo json_encode(['message' => 'Parking introuvable']);
    }

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>

<?php

declare(strict_types=1);
// Front controller minimal
// Autoload (si composer installé)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
}
// Simple bootstrap
echo "Parkingtest application - public index. Configure your router to use config/routes.php";

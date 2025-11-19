<?php
// Configuration minimale du conteneur (retourne un tableau de services)
use App\Infrastructure\Database\Database;
return [
    'db' => Database::getInstance(),
];

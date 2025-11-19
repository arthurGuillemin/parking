<?php
// Définition minimale des routes
use App\Interface\Controller\dbHealthController;

return [
    ['GET', '/', 'HomeController::index'],
    ['GET', '/login', 'AuthController::loginForm'],
    ['POST', '/login', 'AuthController::login'],
    ['GET', '/parkings', 'ParkingController::list'],
    ['GET', '/reservation', 'ReservationController::show'],
    ['GET', '/health', dbHealthController::class, 'check'],

];
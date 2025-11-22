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
    ['POST', '/owner/register', 'OwnerController::register'],
    ['POST', '/owner/login', 'OwnerController::login'],
    ['POST', '/parking/add', 'ParkingController::add'],
    ['POST', '/pricing-rule/update', 'PricingRuleController::update'],
    ['POST', '/opening-hour/update', 'OpeningHourController::update'],
    ['GET', '/reservation/list', 'ReservationController::listByParking'],
    ['GET', '/parking-session/list', 'ParkingSessionController::listByParking'],
    ['GET', '/parking/available-spots', 'ParkingAvailabilityController::getAvailableSpots'],
    ['GET', '/parking/monthly-revenue', 'MonthlyRevenueController::get'],
    ['POST', '/subscription-type/add', 'SubscriptionTypeController::add'],
    ['GET', '/parking/sessions-out-of-reservation-or-subscription', 'SessionsOutOfReservationOrSubscriptionController::list'],

];

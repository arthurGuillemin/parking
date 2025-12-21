<?php
// Définition minimale des routes
use App\Interface\Controller\dbHealthController;

return [
    ['GET', '/', 'HomeController::index'],
    ['GET', '/health', dbHealthController::class, 'check'],

    // Authentification utilisateur
    ['GET', '/login', 'AuthController::loginForm'],
    ['POST', '/login', 'AuthController::login'],
    ['POST', '/logout', 'AuthController::logout'],
    ['POST', '/token/refresh', 'RefreshTokenController::refresh'],

    // Inscription utilisateur
    ['GET', '/register', 'RegisterController::registerForm'],
    ['POST', '/user/register', 'RegisterController::register'],

    // Owner routes
    ['GET', '/owner/register', 'OwnerController::registerForm'],
    ['GET', '/owner/dashboard', 'OwnerController::dashboard'], // Dashboard 

    ['GET', '/owner/parkings', 'ParkingController::listOwnedParkings'], // API for Dashboard

    ['GET', '/parking/add', 'ParkingController::addForm'], // Add Parking Page
    ['GET', '/parking/:id/manage', 'ParkingController::manage'], // Manage Parking Page

    // Authentification propriétaire
    ['POST', '/owner/register', 'OwnerController::register'],

    // Routes utilisateur
    ['GET', '/parkings', 'ParkingController::list'],
    ['GET', '/reservation', 'ReservationController::show'],
    ['POST', '/reservation/create', 'ReservationController::create'],
    ['POST', '/parking/add', 'ParkingController::add'],
    ['POST', '/parking/update', 'ParkingController::update'],
    ['POST', '/pricing-rule/update', 'PricingRuleController::update'],
    ['GET', '/pricing-rule/list', 'PricingRuleController::list'],
    ['GET', '/pricing-rule/list', 'PricingRuleController::list'],
    ['POST', '/opening-hour/add', 'OpeningHourController::add'],
    ['POST', '/opening-hour/delete', 'OpeningHourController::delete'],
    ['GET', '/opening-hour/list', 'OpeningHourController::list'],
    ['GET', '/reservation/list', 'ReservationController::listByParking'],
    ['GET', '/parking-session/list', 'ParkingSessionController::listByParking'],
    ['GET', '/parking/available-spots', 'ParkingAvailabilityController::getAvailableSpots'],
    ['GET', '/monthly-revenue/get', 'MonthlyRevenueController::get'],
    ['POST', '/subscription-type/add', 'SubscriptionTypeController::add'],
    ['GET', '/subscription-type/list', 'SubscriptionTypeController::list'],
    ['GET', '/subscription-type/:id', 'SubscriptionTypeController::getById'],
    ['POST', '/subscription-slot/add', 'SubscriptionSlotController::add'],
    ['GET', '/subscription-slot/:typeId', 'SubscriptionSlotController::getByTypeId'],
    ['DELETE', '/subscription-slot/:id', 'SubscriptionSlotController::delete'],
    ['GET', '/subscription', 'SubscriptionController::showPurchaseForm'], // Shows the form
    ['POST', '/subscription/purchase', 'SubscriptionController::purchase'], // Handles form submit
    ['POST', '/subscription/create', 'SubscriptionController::subscribe'], // API
    ['GET', '/subscription/my-subscriptions', 'SubscriptionController::list'],
    ['GET', '/subscription/:id', 'SubscriptionController::getById'],
    ['DELETE', '/subscription/:id', 'SubscriptionController::cancel'],
    ['GET', '/dashboard', 'UserController::dashboard'],
    ['GET', '/simulation', 'ParkingSessionController::simulation'],
    ['POST', '/parking/enter', 'ParkingSessionController::enter'],
    ['POST', '/parking/exit', 'ParkingSessionController::exit'],
    ['GET', '/parking/sessions-out-of-reservation-or-subscription', 'SessionsOutOfReservationOrSubscriptionController::list'],
    ['POST', '/reservation/create', 'ReservationController::create'],


    // Invoice Download
    ['GET', '/invoices/:id/download', 'InvoiceController::download'],
];

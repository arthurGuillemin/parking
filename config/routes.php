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
    ['GET', '/owner/dashboard', 'OwnerController::dashboard'],
    ['GET', '/owner/parkings', 'ParkingController::listOwnedParkings'],

    // Parking management
    ['GET', '/parking/add', 'ParkingController::addForm'],
    ['GET', '/parking/:id/manage', 'ParkingController::manage'],
    ['POST', '/parking/add', 'ParkingController::add'],
    ['POST', '/parking/update', 'ParkingController::update'],

    // Authentification propriétaire
    ['POST', '/owner/register', 'OwnerController::register'],

    // Routes utilisateur - Parkings
    ['GET', '/parkings', 'ParkingController::list'],
    ['GET', '/parking/available-spots', 'ParkingAvailabilityController::getAvailableSpots'],

    // Reservations
    ['GET', '/reservation', 'ReservationController::show'],
    ['POST', '/reservation/create', 'ReservationController::create'],
    ['GET', '/reservation/list', 'ReservationController::listByParking'],

    // Pricing rules
    ['POST', '/pricing-rule/update', 'PricingRuleController::update'],
    ['GET', '/pricing-rule/list', 'PricingRuleController::list'],

    // Opening hours
    ['POST', '/opening-hour/add', 'OpeningHourController::add'],
    ['POST', '/opening-hour/delete', 'OpeningHourController::delete'],
    ['GET', '/opening-hour/list', 'OpeningHourController::list'],

    // Parking sessions
    ['GET', '/parking-session/list', 'ParkingSessionController::listByParking'],
    ['GET', '/parking/sessions-out-of-reservation-or-subscription', 'SessionsOutOfReservationOrSubscriptionController::list'],

    // Entry/Exit (using ParkingEntryExitController)
    ['POST', '/parking/enter', 'ParkingEntryExitController::enter'],
    ['POST', '/parking/exit', 'ParkingEntryExitController::exit'],

    // Revenue
    ['GET', '/monthly-revenue/get', 'MonthlyRevenueController::get'],

    // Subscription types
    ['POST', '/subscription-type/add', 'SubscriptionTypeController::add'],
    ['GET', '/subscription-type/list', 'SubscriptionTypeController::list'],
    ['GET', '/subscription-type/:id', 'SubscriptionTypeController::getById'],

    // Subscription slots
    ['POST', '/subscription-slot/add', 'SubscriptionSlotController::add'],
    ['GET', '/subscription-slot/:typeId', 'SubscriptionSlotController::getByTypeId'],
    ['DELETE', '/subscription-slot/:id', 'SubscriptionSlotController::delete'],

    // Subscriptions
    ['GET', '/subscription', 'SubscriptionController::showPurchaseForm'],
    ['POST', '/subscription/purchase', 'SubscriptionController::purchase'],
    ['POST', '/subscription/create', 'SubscriptionController::subscribe'],
    ['GET', '/subscription/my-subscriptions', 'SubscriptionController::list'],
    ['GET', '/subscription/:id', 'SubscriptionController::getById'],
    ['DELETE', '/subscription/:id', 'SubscriptionController::cancel'],

    // User dashboard & simulation
    ['GET', '/dashboard', 'UserController::dashboard'],
    ['GET', '/simulation', 'ParkingSessionController::simulation'],

    // Invoice Download
    ['GET', '/invoices/:id/download', 'InvoiceController::download'],
];

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
    /* Fatal error: Uncaught ArgumentCountError: Too few arguments to function App\Domain\Service\OwnerService::__construct(), 1 passed in /Users/antoine/Developer/parking/src/Infrastructure/Container/ServiceContainer.php on line 98 and exactly 3 expected in /Users/antoine/Developer/parking/src/Domain/Service/OwnerService.php:19 Stack trace: #0 /Users/antoine/Developer/parking/src/Infrastructure/Container/ServiceContainer.php(98): App\Domain\Service\OwnerService->__construct(Object(App\Infrastructure\Persistence\Sql\SqlOwnerRepository)) #1 /Users/antoine/Developer/parking/src/Infrastructure/Container/ServiceContainer.php(154): App\Infrastructure\Container\ServiceContainer->{closure:App\Infrastructure\Container\ServiceContainer::registerServices():97}() #2 /Users/antoine/Developer/parking/src/Infrastructure/Container/ServiceContainer.php(130): App\Infrastructure\Container\ServiceContainer->get('App\\Domain\\Serv...') #3 /Users/antoine/Developer/parking/src/Infrastructure/Container/ServiceContainer.php(154): App\Infrastructure\Container\ServiceContainer->{closure:App\Infrastructure\Container\ServiceContainer::registerServices():128}() #4 /Users/antoine/Developer/parking/public/index.php(43): App\Infrastructure\Container\ServiceContainer->get('App\\Interface\\C...') #5 /Users/antoine/Developer/parking/public/index.php(120): resolveController('App\\Interface\\C...', Object(App\Infrastructure\Container\ServiceContainer)) #6 {main} thrown in /Users/antoine/Developer/parking/src/Domain/Service/OwnerService.php on line 19*/

    ['GET', '/owner/parkings', 'ParkingController::listOwnedParkings'], // API for Dashboard

    ['GET', '/parking/add', 'ParkingController::addForm'], // Add Parking Page
    /* <br/><b>Warning</b>:  Undefined array key "name" in <b>/Users/antoine/Developer/parking/src/Interface/Controller/ParkingController.php</b> on line <b>40</b><br/><br/><b>Warning</b>:  Undefined array key "address" in <b>/Users/antoine/Developer/parking/src/Interface/Controller/ParkingController.php</b> on line <b>41</b><br/><br/><b>Warning</b>:  Undefined array key "latitude" in <b>/Users/antoine/Developer/parking/src/Interface/Controller/ParkingController.php</b> on line <b>42</b><br/><br/><b>Warning</b>:  Undefined array key "longitude" in <b>/Users/antoine/Developer/parking/src/Interface/Controller/ParkingController.php</b> on line <b>43</b><br/><br/><b>Warning</b>:  Undefined array key "totalCapacity" in <b>/Users/antoine/Developer/parking/src/Interface/Controller/ParkingController.php</b> on line <b>44</b><br/><br/><b>Warning</b>:  http_response_code(): Cannot set response code - headers already sent (output started at /Users/antoine/Developer/parking/src/Interface/Controller/ParkingController.php: 40)in<b>/Users/antoine/Developer/parking/src/Interface/Controller/ParkingController.php</b> on line <b>56</b><br/>{
    "error": "Erreur serveur: App\\Domain\\Service\\ParkingService::addParking(): Argument #2 ($name) must be of type string, null given, called in \/Users\/antoine\/Developer\/parking\/src\/Interface\/Controller\/ParkingController.php on line 38"
} */
    ['GET', '/parking/:id/manage', 'ParkingController::manage'], // Manage Parking Page

    // Authentification propriétaire
    // ['GET', '/owner/login', 'OwnerController::loginForm'], // SUPPRIMÉ : Centralisé sur /login
    ['POST', '/owner/register', 'OwnerController::register'],
    // ['POST', '/owner/login', 'OwnerController::login'], // SUPPRIMÉ : Centralisé sur /login

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
    ['GET', '/parking/monthly-revenue', 'MonthlyRevenueController::get'],
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
];

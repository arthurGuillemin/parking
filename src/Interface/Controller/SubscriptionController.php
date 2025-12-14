<?php

namespace App\Interface\Controller;

use App\Application\UseCase\User\AddSubscription\AddSubscriptionUseCase;
use App\Application\UseCase\User\AddSubscription\AddSubscriptionRequest;
use App\Application\UseCase\User\ListUserSubscriptions\ListUserSubscriptionsUseCase;
use App\Application\UseCase\User\ListUserSubscriptions\ListUserSubscriptionsRequest;
use App\Application\UseCase\User\GetSubscription\GetSubscriptionUseCase;
use App\Application\UseCase\User\GetSubscription\GetSubscriptionRequest;
use App\Application\UseCase\User\CancelSubscription\CancelSubscriptionUseCase;
use App\Application\UseCase\User\CancelSubscription\CancelSubscriptionRequest;
use App\Interface\Presenter\SubscriptionPresenter;

class SubscriptionController
{
    private AddSubscriptionUseCase $addSubscriptionUseCase;
    private ListUserSubscriptionsUseCase $listUserSubscriptionsUseCase;
    private GetSubscriptionUseCase $getSubscriptionUseCase;
    private CancelSubscriptionUseCase $cancelSubscriptionUseCase;
    private \App\Application\UseCase\Owner\ListSubscriptionTypes\ListSubscriptionTypesUseCase $listSubscriptionTypesUseCase;
    private \App\Domain\Service\ParkingService $parkingService;
    private \App\Domain\Service\JwtService $jwtService;
    private SubscriptionPresenter $presenter;

    public function __construct(
        AddSubscriptionUseCase $addSubscriptionUseCase,
        ListUserSubscriptionsUseCase $listUserSubscriptionsUseCase,
        GetSubscriptionUseCase $getSubscriptionUseCase,
        CancelSubscriptionUseCase $cancelSubscriptionUseCase,
        \App\Application\UseCase\Owner\ListSubscriptionTypes\ListSubscriptionTypesUseCase $listSubscriptionTypesUseCase,
        \App\Domain\Service\ParkingService $parkingService,
        \App\Domain\Service\JwtService $jwtService,
        SubscriptionPresenter $presenter
    ) {
        $this->addSubscriptionUseCase = $addSubscriptionUseCase;
        $this->listUserSubscriptionsUseCase = $listUserSubscriptionsUseCase;
        $this->getSubscriptionUseCase = $getSubscriptionUseCase;
        $this->cancelSubscriptionUseCase = $cancelSubscriptionUseCase;
        $this->listSubscriptionTypesUseCase = $listSubscriptionTypesUseCase;
        $this->parkingService = $parkingService;
        $this->jwtService = $jwtService;
        $this->presenter = $presenter;
    }

    public function showPurchaseForm(array $data): void
    {
        // Extract parkingId from route params or query
        $parkingId = $data['parkingId'] ?? $_GET['parkingId'] ?? null;
        if (!$parkingId) {
            header('Location: /parkings');
            return;
        }

        $parking = $this->parkingService->getParkingById((int) $parkingId);
        if (!$parking) {
            die("Parking not found");
        }

        // List types (currently implementation returns all, but we should eventually filter)
        // Since findAll is global, we might show keys that don't belong?
        // But for now schema seems shared.
        // We'll rename ListSubscriptionTypesRequest input to parkingId if it matters.
        // The Request object takes parkingId.
        $request = new \App\Application\UseCase\Owner\ListSubscriptionTypes\ListSubscriptionTypesRequest((int) $parkingId);
        $subscriptionTypes = $this->listSubscriptionTypesUseCase->execute($request);

        require dirname(__DIR__, 3) . '/templates/subscription_purchase.php';
    }

    public function purchase(array $data): void
    {
        // Auth check
        $userId = null;
        if (isset($_COOKIE['auth_token'])) {
            $payload = $this->jwtService->decode($_COOKIE['auth_token']);
            if ($payload) {
                $userId = $payload['user_id'] ?? null;
            }
        }
        if (!$userId) {
            header('Location: /login?error=auth_required');
            return;
        }

        try {
            // Data from Form
            $parkingId = (int) ($data['parkingId'] ?? $_POST['parkingId']);
            $typeId = (int) ($data['typeId'] ?? $_POST['typeId']);
            $startDateStr = $data['startDate'] ?? $_POST['startDate'] ?? 'now';
            $startDate = new \DateTimeImmutable($startDateStr);

            // We need the Type to know the price
            // Re-fetch types to find the selected one and its price
            $requestList = new \App\Application\UseCase\Owner\ListSubscriptionTypes\ListSubscriptionTypesRequest($parkingId);
            $types = $this->listSubscriptionTypesUseCase->execute($requestList);

            $selectedType = null;
            foreach ($types as $t) {
                if ($t->id === $typeId) {
                    $selectedType = $t;
                    break;
                }
            }

            if (!$selectedType) {
                throw new \Exception("Invalid Subscription Type");
            }

            // Calculate End Date (Default 1 month? Or user selected?)
            // Form implies monthly.
            // Let's set it to null (infinite) if logic supports it, or request implementation requires end date.
            // AddSubscriptionUseCase checks: min 1 month, max 1 year.
            // So let's default to 1 month for now, or 1 year?
            // "L'abonnement est mensuel avec tacite reconduction" -> usually means indefinite.
            // usage: $request->endDate.
            // If I pass null, UseCase defaults to 1 Year.
            $endDate = null;

            $request = new AddSubscriptionRequest(
                $userId,
                $parkingId,
                $typeId,
                $startDate,
                $endDate,
                $selectedType->monthlyPrice
            );

            $this->addSubscriptionUseCase->execute($request);

            // Redirect to dashboard (or list for now)
            header('Location: /subscription/my-subscriptions'); // Or a success page

        } catch (\Exception $e) {
            die("Erreur: " . $e->getMessage());
        }
    }

    public function subscribe(array $data): array
    {
        // API Method - Kept for compatibility but might need update if used
        $required = ['userId', 'parkingId', 'monthlyPrice'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Le champ $field est obligatoire.");
            }
        }

        $startDate = new \DateTimeImmutable($data['startDate'] ?? 'now');
        $endDate = !empty($data['endDate']) ? new \DateTimeImmutable($data['endDate']) : null;

        $request = new AddSubscriptionRequest(
            $data['userId'],
            (int) $data['parkingId'],
            $data['typeId'] ?? null,
            $startDate,
            $endDate,
            (float) $data['monthlyPrice']
        );

        $response = $this->addSubscriptionUseCase->execute($request);

        return $this->presenter->present($response);
    }

    public function list(array $data): void
    {
        // Modified to be View-friendly OR API-friendly
        // If accessing via browser (no Content-Type json), render view?
        // For now, let's look at how it's called. 
        // Route: GET /subscription/my-subscriptions -> SubscriptionController::list
        // If checking cookie, we can deduce userId.

        $userId = null;
        if (isset($_COOKIE['auth_token'])) {
            $payload = $this->jwtService->decode($_COOKIE['auth_token']);
            $userId = $payload['user_id'] ?? null;
        }

        // Use data['userId'] if provided (API) or cookie
        $userId = $data['userId'] ?? $userId;

        if (!$userId) {
            header('Location: /login');
            return;
        }

        $request = new ListUserSubscriptionsRequest($userId);
        $responses = $this->listUserSubscriptionsUseCase->execute($request);

        // If query param ?format=json, return json. Else render view.
        if (isset($_GET['format']) && $_GET['format'] === 'json') {
            // Return array for router to json_encode
            $this->presenterToArray($responses);
            return; // Router handles return
        }

        // Render View
        $subscriptions = $responses; // DTOs
        require dirname(__DIR__, 3) . '/templates/subscription_list_user.php';
    }

    private function presenterToArray($responses): array
    {
        return array_map(function ($response) {
            return $this->presenter->present($response);
        }, $responses);
    }

    public function getById(array $data): array
    {
        if (empty($data['id'])) {
            throw new \InvalidArgumentException('Le paramètre id est obligatoire.');
        }

        $request = new GetSubscriptionRequest((int) $data['id']);
        $response = $this->getSubscriptionUseCase->execute($request);

        return $this->presenter->present($response);
    }

    public function cancel(array $data): array
    {
        if (empty($data['id'])) {
            throw new \InvalidArgumentException('Le paramètre id est obligatoire.');
        }

        $request = new CancelSubscriptionRequest((int) $data['id']);
        $response = $this->cancelSubscriptionUseCase->execute($request);

        return $this->presenter->present($response);
    }
}

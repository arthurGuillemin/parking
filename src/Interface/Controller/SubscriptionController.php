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

    /**
     * Affiche le formulaire d'achat d'abonnement
     */
    public function showPurchaseForm(array $data): void
    {
        $parkingId = $data['parkingId'] ?? $_GET['parkingId'] ?? null;
        if (!$parkingId) {
            header('Location: /parkings');
            return;
        }

        $parking = $this->parkingService->getParkingById((int) $parkingId);
        if (!$parking) {
            die("Parking not found");
        }

        $request = new \App\Application\UseCase\Owner\ListSubscriptionTypes\ListSubscriptionTypesRequest((int) $parkingId);
        $subscriptionTypes = $this->listSubscriptionTypesUseCase->execute($request);

        require dirname(__DIR__, 3) . '/templates/subscription_purchase.php';
    }

    /**
     * Traite l'achat d'un abonnement
     */
    public function purchase(array $data): void
    {
        $userId = $this->getUserIdFromToken();
        if (!$userId) {
            header('Location: /login?error=auth_required');
            return;
        }

        try {
            $purchaseData = $this->parsePurchaseData($data);
            $selectedType = $this->findSubscriptionType($purchaseData['parkingId'], $purchaseData['typeId']);

            if (!$selectedType) {
                throw new \Exception("Invalid Subscription Type");
            }

            $request = new AddSubscriptionRequest(
                $userId,
                $purchaseData['parkingId'],
                $purchaseData['typeId'],
                $purchaseData['startDate'],
                null,
                $selectedType->monthlyPrice
            );

            $this->addSubscriptionUseCase->execute($request);
            header('Location: /subscription/my-subscriptions');
        } catch (\Exception $e) {
            die("Erreur: " . $e->getMessage());
        }
    }

    /**
     * API - Crée un abonnement
     */
    public function subscribe(array $data): array
    {
        $this->validateRequiredFields($data, ['userId', 'parkingId', 'monthlyPrice']);

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

    /**
     * Liste les abonnements de l'utilisateur
     */
    public function list(array $data): void
    {
        $userId = $data['userId'] ?? $this->getUserIdFromToken();

        if (!$userId) {
            header('Location: /login');
            return;
        }

        $request = new ListUserSubscriptionsRequest($userId);
        $responses = $this->listUserSubscriptionsUseCase->execute($request);

        if ($this->isJsonRequest()) {
            $this->presenterToArray($responses);
            return;
        }

        $subscriptions = $responses;
        require dirname(__DIR__, 3) . '/templates/subscription_list_user.php';
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

    /**
     * Récupère l'ID utilisateur depuis le token JWT
     */
    private function getUserIdFromToken(): ?string
    {
        if (!isset($_COOKIE['auth_token'])) {
            return null;
        }
        $payload = $this->jwtService->decode($_COOKIE['auth_token']);
        return $payload['user_id'] ?? null;
    }

    /**
     * Parse les données du formulaire d'achat
     */
    private function parsePurchaseData(array $data): array
    {
        return [
            'parkingId' => (int) ($data['parkingId'] ?? $_POST['parkingId']),
            'typeId' => (int) ($data['typeId'] ?? $_POST['typeId']),
            'startDate' => new \DateTimeImmutable($data['startDate'] ?? $_POST['startDate'] ?? 'now'),
        ];
    }

    /**
     * Recherche un type d'abonnement par ID
     */
    private function findSubscriptionType(int $parkingId, int $typeId)
    {
        $request = new \App\Application\UseCase\Owner\ListSubscriptionTypes\ListSubscriptionTypesRequest($parkingId);
        $types = $this->listSubscriptionTypesUseCase->execute($request);

        foreach ($types as $type) {
            if ($type->id === $typeId) {
                return $type;
            }
        }
        return null;
    }

    /**
     * Valide les champs requis
     */
    private function validateRequiredFields(array $data, array $required): void
    {
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Le champ $field est obligatoire.");
            }
        }
    }

    /**
     * Vérifie si la requête demande du JSON
     */
    private function isJsonRequest(): bool
    {
        return isset($_GET['format']) && $_GET['format'] === 'json';
    }

    private function presenterToArray($responses): array
    {
        return array_map(function ($response) {
            return $this->presenter->present($response);
        }, $responses);
    }
}

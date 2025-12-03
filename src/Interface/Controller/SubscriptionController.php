<?php

namespace App\Interface\Controller;

use App\Application\UseCase\User\AddSubscription\AddSubscriptionUseCase;
use App\Application\UseCase\User\AddSubscription\AddSubscriptionRequest;
use App\Application\UseCase\User\ListUserSubscriptions\ListUserSubscriptionsUseCase;
use App\Application\UseCase\User\ListUserSubscriptions\ListUserSubscriptionsRequest;

class SubscriptionController
{
    private AddSubscriptionUseCase $addSubscriptionUseCase;
    private ListUserSubscriptionsUseCase $listUserSubscriptionsUseCase;

    public function __construct(
        AddSubscriptionUseCase $addSubscriptionUseCase,
        ListUserSubscriptionsUseCase $listUserSubscriptionsUseCase
    ) {
        $this->addSubscriptionUseCase = $addSubscriptionUseCase;
        $this->listUserSubscriptionsUseCase = $listUserSubscriptionsUseCase;
    }

    public function subscribe(array $data): array
    {
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
            (int)$data['parkingId'],
            $data['typeId'] ?? null,
            $startDate,
            $endDate,
            (float)$data['monthlyPrice']
        );

        $subscription = $this->addSubscriptionUseCase->execute($request);

        return [
            'id' => $subscription->getSubscriptionId(),
            'userId' => $subscription->getUserId(),
            'parkingId' => $subscription->getParkingId(),
            'typeId' => $subscription->getTypeId(),
            'startDate' => $subscription->getStartDate()->format('Y-m-d'),
            'endDate' => $subscription->getEndDate()?->format('Y-m-d'),
            'status' => $subscription->getStatus(),
            'monthlyPrice' => $subscription->getMonthlyPrice(),
        ];
    }

    public function list(array $data): array
    {
        if (empty($data['userId'])) {
            throw new \InvalidArgumentException('Le champ userId est obligatoire.');
        }

        $request = new ListUserSubscriptionsRequest($data['userId']);
        $subscriptions = $this->listUserSubscriptionsUseCase->execute($request);

        return array_map(function ($sub) {
            return [
                'id' => $sub->getSubscriptionId(),
                'parkingId' => $sub->getParkingId(),
                'typeId' => $sub->getTypeId(),
                'startDate' => $sub->getStartDate()->format('Y-m-d'),
                'endDate' => $sub->getEndDate()?->format('Y-m-d'),
                'status' => $sub->getStatus(),
                'monthlyPrice' => $sub->getMonthlyPrice(),
            ];
        }, $subscriptions);
    }

    public function getById(array $data): array
    {
        if (empty($data['id'])) {
            throw new \InvalidArgumentException('Le paramètre id est obligatoire.');
        }

        // À implémenter : GetSubscriptionUseCase
        return [];
    }

    public function cancel(array $data): array
    {
        if (empty($data['id'])) {
            throw new \InvalidArgumentException('Le paramètre id est obligatoire.');
        }

        // À implémenter : CancelSubscriptionUseCase
        return ['success' => true, 'message' => 'Abonnement annulé.'];
    }
}

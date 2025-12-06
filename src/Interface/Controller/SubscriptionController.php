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
    private SubscriptionPresenter $presenter;

    public function __construct(
        AddSubscriptionUseCase $addSubscriptionUseCase,
        ListUserSubscriptionsUseCase $listUserSubscriptionsUseCase,
        GetSubscriptionUseCase $getSubscriptionUseCase,
        CancelSubscriptionUseCase $cancelSubscriptionUseCase,
        SubscriptionPresenter $presenter
    ) {
        $this->addSubscriptionUseCase = $addSubscriptionUseCase;
        $this->listUserSubscriptionsUseCase = $listUserSubscriptionsUseCase;
        $this->getSubscriptionUseCase = $getSubscriptionUseCase;
        $this->cancelSubscriptionUseCase = $cancelSubscriptionUseCase;
        $this->presenter = $presenter;
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
            (int) $data['parkingId'],
            $data['typeId'] ?? null,
            $startDate,
            $endDate,
            (float) $data['monthlyPrice']
        );

        $response = $this->addSubscriptionUseCase->execute($request);

        return $this->presenter->present($response);
    }

    public function list(array $data): array
    {
        if (empty($data['userId'])) {
            throw new \InvalidArgumentException('Le champ userId est obligatoire.');
        }

        $request = new ListUserSubscriptionsRequest($data['userId']);
        $responses = $this->listUserSubscriptionsUseCase->execute($request);

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

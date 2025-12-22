<?php

namespace App\Interface\Controller;

use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeUseCase;
use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeRequest;
use App\Application\UseCase\Owner\ListSubscriptionTypes\ListSubscriptionTypesUseCase;
use App\Application\UseCase\Owner\ListSubscriptionTypes\ListSubscriptionTypesRequest;
use App\Application\UseCase\Owner\GetSubscriptionType\GetSubscriptionTypeUseCase;
use App\Application\UseCase\Owner\GetSubscriptionType\GetSubscriptionTypeRequest;
use App\Interface\Presenter\SubscriptionTypePresenter;

class SubscriptionTypeController
{
    private SubscriptionTypePresenter $presenter;
    private AddSubscriptionTypeUseCase $addSubscriptionTypeUseCase;
    private ListSubscriptionTypesUseCase $listSubscriptionTypesUseCase;
    private GetSubscriptionTypeUseCase $getSubscriptionTypeUseCase;

    public function __construct(
        AddSubscriptionTypeUseCase $addSubscriptionTypeUseCase,
        ListSubscriptionTypesUseCase $listSubscriptionTypesUseCase,
        GetSubscriptionTypeUseCase $getSubscriptionTypeUseCase,
        SubscriptionTypePresenter $presenter
    ) {
        $this->addSubscriptionTypeUseCase = $addSubscriptionTypeUseCase;
        $this->listSubscriptionTypesUseCase = $listSubscriptionTypesUseCase;
        $this->getSubscriptionTypeUseCase = $getSubscriptionTypeUseCase;
        $this->presenter = $presenter;
    }

    public function add(array $data): array
    {
        if (empty($data['parkingId']) || empty($data['name'])) {
            throw new \InvalidArgumentException('Les champs parkingId et name sont obligatoires.');
        }

        $price = (float) ($data['price'] ?? 0.0);
        if ($price <= 0) {
            throw new \InvalidArgumentException('Le prix doit être supérieur à 0€.');
        }

        $request = new AddSubscriptionTypeRequest(
            (int) $data['parkingId'],
            $data['name'],
            $data['description'] ?? null,
            $price
        );

        $response = $this->addSubscriptionTypeUseCase->execute($request);

        return $this->presenter->present($response);
    }

    public function list(array $data): array
    {
        $parkingId = !empty($data['parkingId']) ? (int) $data['parkingId'] : null;
        $request = new ListSubscriptionTypesRequest($parkingId);
        $responses = $this->listSubscriptionTypesUseCase->execute($request);

        return array_map(function ($response) {
            return $this->presenter->present($response);
        }, $responses);
    }

    public function getById(array $data): array
    {
        if (empty($data['id'])) {
            throw new \InvalidArgumentException('Le paramètre id est obligatoire.');
        }

        $request = new GetSubscriptionTypeRequest((int) $data['id']);
        $response = $this->getSubscriptionTypeUseCase->execute($request);

        return $this->presenter->present($response);
    }
}

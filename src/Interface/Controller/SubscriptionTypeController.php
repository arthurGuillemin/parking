<?php

namespace App\Interface\Controller;

use App\Domain\Service\SubscriptionTypeService;
use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeRequest;
use Exception;

class SubscriptionTypeController
{
    private SubscriptionTypeService $subscriptionTypeService;

    public function __construct(SubscriptionTypeService $subscriptionTypeService)
    {
        $this->subscriptionTypeService = $subscriptionTypeService;
    }

    public function add(array $data): array
    {
        if (empty($data['parkingId']) || empty($data['name'])) {
            throw new Exception('Les champs parkingId et name sont obligatoires.');
        }
        $request = new AddSubscriptionTypeRequest((int)$data['parkingId'], $data['name'], $data['description'] ?? null);
        $type = $this->subscriptionTypeService->addSubscriptionType($request);
        return [
            'id' => $type->getSubscriptionTypeId(),
            'name' => $type->getName(),
            'description' => $type->getDescription(),
        ];
    }
}


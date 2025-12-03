<?php

namespace App\Interface\Controller;

use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeUseCase;
use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeRequest;

class SubscriptionTypeController
{
    private AddSubscriptionTypeUseCase $addSubscriptionTypeUseCase;

    public function __construct(AddSubscriptionTypeUseCase $addSubscriptionTypeUseCase)
    {
        $this->addSubscriptionTypeUseCase = $addSubscriptionTypeUseCase;
    }

    public function add(array $data): array
    {
        if (empty($data['parkingId']) || empty($data['name'])) {
            throw new \InvalidArgumentException('Les champs parkingId et name sont obligatoires.');
        }

        $request = new AddSubscriptionTypeRequest(
            (int)$data['parkingId'],
            $data['name'],
            $data['description'] ?? null
        );

        $type = $this->addSubscriptionTypeUseCase->execute($request);

        return [
            'id' => $type->getSubscriptionTypeId(),
            'parkingId' => $type->getParkingId(),
            'name' => $type->getName(),
            'description' => $type->getDescription(),
        ];
    }

    public function list(array $data): array
    {
        // À implémenter : ListSubscriptionTypesUseCase
        // Retourner tous les types d'abonnement
        return [];
    }

    public function getById(array $data): array
    {
        if (empty($data['id'])) {
            throw new \InvalidArgumentException('Le paramètre id est obligatoire.');
        }

        // À implémenter : GetSubscriptionTypeUseCase
        return [];
    }
}

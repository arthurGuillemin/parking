<?php

namespace App\Interface\Controller;

use App\Application\UseCase\Owner\AddSubscriptionSlot\AddSubscriptionSlotUseCase;
use App\Application\UseCase\Owner\AddSubscriptionSlot\AddSubscriptionSlotRequest;

class SubscriptionSlotController
{
    private AddSubscriptionSlotUseCase $addSubscriptionSlotUseCase;

    public function __construct(AddSubscriptionSlotUseCase $addSubscriptionSlotUseCase)
    {
        $this->addSubscriptionSlotUseCase = $addSubscriptionSlotUseCase;
    }

    public function add(array $data): array
    {
        $required = ['subscriptionTypeId', 'weekday', 'startTime', 'endTime'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Le champ $field est obligatoire.");
            }
        }

        $startTime = new \DateTimeImmutable($data['startTime']);
        $endTime = new \DateTimeImmutable($data['endTime']);

        $request = new AddSubscriptionSlotRequest(
            (int)$data['subscriptionTypeId'],
            (int)$data['weekday'],
            $startTime,
            $endTime
        );

        $slot = $this->addSubscriptionSlotUseCase->execute($request);

        return [
            'id' => $slot->getSubscriptionSlotId(),
            'subscriptionTypeId' => $slot->getSubscriptionTypeId(),
            'weekday' => $slot->getWeekday(),
            'startTime' => $slot->getStartTime()->format('H:i:s'),
            'endTime' => $slot->getEndTime()->format('H:i:s'),
        ];
    }

    public function getByTypeId(array $data): array
    {
        if (empty($data['typeId'])) {
            throw new \InvalidArgumentException('Le paramètre typeId est obligatoire.');
        }

        // À implémenter : ListSlotsByTypeUseCase
        return [];
    }

    public function delete(array $data): array
    {
        if (empty($data['id'])) {
            throw new \InvalidArgumentException('Le paramètre id est obligatoire.');
        }

        // À implémenter : DeleteSlotUseCase
        return ['success' => true];
    }
}

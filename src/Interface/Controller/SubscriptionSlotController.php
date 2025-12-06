<?php

namespace App\Interface\Controller;

use App\Application\UseCase\Owner\AddSubscriptionSlot\AddSubscriptionSlotUseCase;
use App\Application\UseCase\Owner\AddSubscriptionSlot\AddSubscriptionSlotRequest;
use App\Application\UseCase\Owner\ListSubscriptionSlots\ListSubscriptionSlotsUseCase;
use App\Application\UseCase\Owner\ListSubscriptionSlots\ListSubscriptionSlotsRequest;
use App\Application\UseCase\Owner\DeleteSubscriptionSlot\DeleteSubscriptionSlotUseCase;
use App\Application\UseCase\Owner\DeleteSubscriptionSlot\DeleteSubscriptionSlotRequest;
use App\Interface\Presenter\SubscriptionSlotPresenter;

class SubscriptionSlotController
{
    private SubscriptionSlotPresenter $presenter;
    private AddSubscriptionSlotUseCase $addSubscriptionSlotUseCase;
    private ListSubscriptionSlotsUseCase $listSubscriptionSlotsUseCase;
    private DeleteSubscriptionSlotUseCase $deleteSubscriptionSlotUseCase;

    public function __construct(
        AddSubscriptionSlotUseCase $addSubscriptionSlotUseCase,
        ListSubscriptionSlotsUseCase $listSubscriptionSlotsUseCase,
        DeleteSubscriptionSlotUseCase $deleteSubscriptionSlotUseCase,
        SubscriptionSlotPresenter $presenter
    ) {
        $this->addSubscriptionSlotUseCase = $addSubscriptionSlotUseCase;
        $this->listSubscriptionSlotsUseCase = $listSubscriptionSlotsUseCase;
        $this->deleteSubscriptionSlotUseCase = $deleteSubscriptionSlotUseCase;
        $this->presenter = $presenter;
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
            (int) $data['subscriptionTypeId'],
            (int) $data['weekday'],
            $startTime,
            $endTime
        );

        $response = $this->addSubscriptionSlotUseCase->execute($request);

        return $this->presenter->present($response);
    }

    public function getByTypeId(array $data): array
    {
        if (empty($data['typeId'])) {
            throw new \InvalidArgumentException('Le paramètre typeId est obligatoire.');
        }

        $request = new ListSubscriptionSlotsRequest((int) $data['typeId']);
        $responses = $this->listSubscriptionSlotsUseCase->execute($request);

        return array_map(function ($response) {
            return $this->presenter->present($response);
        }, $responses);
    }

    public function delete(array $data): array
    {
        if (empty($data['id'])) {
            throw new \InvalidArgumentException('Le paramètre id est obligatoire.');
        }

        $request = new DeleteSubscriptionSlotRequest((int) $data['id']);
        $this->deleteSubscriptionSlotUseCase->execute($request);

        return ['success' => true];
    }
}

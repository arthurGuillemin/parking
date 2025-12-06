<?php

namespace App\Interface\Presenter;

use App\Application\UseCase\Owner\AddSubscriptionSlot\AddSubscriptionSlotResponse;

class SubscriptionSlotPresenter
{
    public function present(AddSubscriptionSlotResponse $response): array
    {
        return [
            'id' => $response->id,
            'subscriptionTypeId' => $response->subscriptionTypeId,
            'weekday' => $response->weekday,
            'startTime' => $response->startTime,
            'endTime' => $response->endTime,
        ];
    }
}

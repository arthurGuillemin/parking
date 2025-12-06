<?php

namespace App\Interface\Presenter;

use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeResponse;

class SubscriptionTypePresenter
{
    public function present(AddSubscriptionTypeResponse $response): array
    {
        return [
            'id' => $response->id,
            'parkingId' => $response->parkingId,
            'name' => $response->name,
            'description' => $response->description,
        ];
    }
}

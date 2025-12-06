<?php

namespace App\Interface\Presenter;

use App\Application\DTO\Response\SubscriptionResponse;

class SubscriptionPresenter
{
    public function present(SubscriptionResponse $response): array
    {
        return [
            'id' => $response->id,
            'userId' => $response->userId,
            'parkingId' => $response->parkingId,
            'typeId' => $response->typeId,
            'startDate' => $response->startDate,
            'endDate' => $response->endDate,
            'status' => $response->status,
            'monthlyPrice' => $response->monthlyPrice,
        ];
    }
}

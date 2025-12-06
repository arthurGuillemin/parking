<?php

namespace App\Application\UseCase\Owner\AddSubscriptionSlot;

class AddSubscriptionSlotResponse
{
    public int $id;
    public int $subscriptionTypeId;
    public int $weekday;
    public string $startTime;
    public string $endTime;

    public function __construct(
        int $id,
        int $subscriptionTypeId,
        int $weekday,
        string $startTime,
        string $endTime
    ) {
        $this->id = $id;
        $this->subscriptionTypeId = $subscriptionTypeId;
        $this->weekday = $weekday;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }
}

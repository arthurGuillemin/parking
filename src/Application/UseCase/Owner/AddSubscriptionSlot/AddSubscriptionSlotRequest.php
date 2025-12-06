<?php

namespace App\Application\UseCase\Owner\AddSubscriptionSlot;

class AddSubscriptionSlotRequest
{
    public int $subscriptionTypeId;
    public int $weekday; // 1-7
    public \DateTimeImmutable $startTime;
    public \DateTimeImmutable $endTime;

    public function __construct(
        int $subscriptionTypeId,
        int $weekday,
        \DateTimeImmutable $startTime,
        \DateTimeImmutable $endTime
    ) {
        $this->subscriptionTypeId = $subscriptionTypeId;
        $this->weekday = $weekday;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }
}
<?php

namespace App\Domain\Entity;

class SubscriptionSlot
{
    private int $id;
    private int $subscriptionTypeId;
    private int $weekday; // 1 (Lundi) à 7 (Dimanche) - UN SEUL JOUR
    private \DateTimeImmutable $startTime;
    private \DateTimeImmutable $endTime;

    public function __construct(
        int $id,
        int $subscriptionTypeId,
        int $weekday,
        \DateTimeImmutable $startTime,
        \DateTimeImmutable $endTime
    ) {
        $this->id = $id;
        $this->subscriptionTypeId = $subscriptionTypeId;
        $this->weekday = $weekday;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }

    public function getSubscriptionSlotId(): int
    {
        return $this->id;
    }

    public function getSubscriptionTypeId(): int
    {
        return $this->subscriptionTypeId;
    }

    public function getWeekday(): int
    {
        return $this->weekday;
    }

    public function getStartTime(): \DateTimeImmutable
    {
        return $this->startTime;
    }

    public function getEndTime(): \DateTimeImmutable
    {
        return $this->endTime;
    }
}

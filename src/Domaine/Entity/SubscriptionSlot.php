<?php

namespace App\Domaine\Entity;

class SubscriptionSlot {
    private int $id;
    private int $subscriptionId;
    private int $weekdayStart; // 1 (Lundi) à 7 (Dimanche)
    private int $weekdayEnd;   // 1 (Lundi) à 7 (Dimanche)
    private \DateTimeImmutable $startTime; // 'HH:MM:SS'
    private \DateTimeImmutable $endTime;   // 'HH:MM:SS'

    public function __construct(int $id, int $subscriptionId, int $weekdayStart, int $weekdayEnd, \DateTimeImmutable $startTime, \DateTimeImmutable $endTime) {
        $this->id = $id;
        $this->subscriptionId = $subscriptionId;
        $this->weekdayStart = $weekdayStart;
        $this->weekdayEnd = $weekdayEnd;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }

    public function getSubscriptionSlotId(): int {
        return $this->id;
    }

    public function getSubscriptionId(): int {
        return $this->subscriptionId;
    }

    public function getWeekdayStart(): int {
        return $this->weekdayStart;
    }

    public function getWeekdayEnd(): int {
        return $this->weekdayEnd;
    }

    public function getStartTime(): \DateTimeImmutable {
        return $this->startTime;
    }

    public function getEndTime(): \DateTimeImmutable {
        return $this->endTime;
    }
}

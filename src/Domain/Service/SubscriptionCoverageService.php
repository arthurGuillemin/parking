<?php

namespace App\Domain\Service;

use App\Domain\Entity\Subscription;
use App\Domain\Repository\SubscriptionSlotRepositoryInterface;
use DateTimeImmutable;

class SubscriptionCoverageService
{
    private SubscriptionSlotRepositoryInterface $slotRepository;

    public function __construct(SubscriptionSlotRepositoryInterface $slotRepository)
    {
        $this->slotRepository = $slotRepository;
    }

    /**
     * Vérifie si une date/heure est couverte par les créneaux de l'abonnement.
     */
    public function isDateTimeCovered(Subscription $subscription, DateTimeImmutable $dateTime): bool
    {
        if (!$this->isWithinSubscriptionPeriod($subscription, $dateTime)) {
            return false;
        }

        // Un abonnement sans typeId est considéré comme un accès 24/7
        if ($subscription->getTypeId() === null) {
            return true;
        }

        $slots = $this->slotRepository->findBySubscriptionTypeId($subscription->getTypeId());

        if (empty($slots)) {
            return true;
        }

        return $this->isTimeInAnySlot($slots, $dateTime);
    }

    /**
     * Vérifie si la date est dans la période de validité de l'abonnement.
     */
    private function isWithinSubscriptionPeriod(Subscription $subscription, DateTimeImmutable $dateTime): bool
    {
        $startDate = $subscription->getStartDate();
        $endDate = $subscription->getEndDate() ?? new DateTimeImmutable('+1 year');

        return $dateTime >= $startDate && $dateTime <= $endDate;
    }

    /**
     * Vérifie si l'heure correspond à l'un des créneaux du jour.
     */
    private function isTimeInAnySlot(array $slots, DateTimeImmutable $dateTime): bool
    {
        $weekday = (int) $dateTime->format('N');
        $currentTime = $dateTime->format('H:i:s');

        foreach ($slots as $slot) {
            if ($this->isSlotMatching($slot, $weekday, $currentTime)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie si un créneau correspond au jour et à l'heure donnés.
     */
    private function isSlotMatching(object $slot, int $weekday, string $currentTime): bool
    {
        if ($slot->getWeekday() !== $weekday) {
            return false;
        }

        $startTime = $slot->getStartTime()->format('H:i:s');
        $endTime = $slot->getEndTime()->format('H:i:s');

        return $currentTime >= $startTime && $currentTime <= $endTime;
    }
}
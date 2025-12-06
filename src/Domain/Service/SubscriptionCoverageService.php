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
     * Checks if a given datetime is covered by the subscription's time slots.
     */
    public function isDateTimeCovered(Subscription $subscription, DateTimeImmutable $dateTime): bool
    {
        // Check if the subscription is active 
        $endDate = $subscription->getEndDate() ?? new DateTimeImmutable('+1 year'); // Handle null endDate
        if ($dateTime < $subscription->getStartDate() || $dateTime > $endDate) {
            return false;
        }

        // 2. An abonnement can have no typeId if it's a 24/7 subscription
        if ($subscription->getTypeId() === null) {
            return true; // Consider null type as total access
        }
        
        // 3. Get slots for the subscription type
        $slots = $this->slotRepository->findBySubscriptionTypeId($subscription->getTypeId());
        
        if (empty($slots)) {
            return true; // Or false, depending on business rule if a type has no slots
        }

        $weekday = (int)$dateTime->format('N'); // 1 (Mon) to 7 (Sun)
        $time = $dateTime->format('H:i:s');

        // 4. Check if current time falls into any slot for the current day
        foreach ($slots as $slot) {
            if ($slot->getWeekday() === $weekday) {
                $startTime = $slot->getStartTime()->format('H:i:s');
                $endTime = $slot->getEndTime()->format('H:i:s');

                if ($time >= $startTime && $time <= $endTime) {
                    return true;
                }
            }
        }

        return false;
    }
}
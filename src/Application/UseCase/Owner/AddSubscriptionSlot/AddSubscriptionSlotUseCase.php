<?php

namespace App\Application\UseCase\Owner\AddSubscriptionSlot;

use App\Domain\Entity\SubscriptionSlot;
use App\Domain\Repository\SubscriptionSlotRepositoryInterface;

class AddSubscriptionSlotUseCase
{
    private SubscriptionSlotRepositoryInterface $slotRepository;

    public function __construct(SubscriptionSlotRepositoryInterface $slotRepository)
    {
        $this->slotRepository = $slotRepository;
    }

    /**
     * Add a time slot to a subscription type.
     *
     * @param AddSubscriptionSlotRequest $request
     * @return SubscriptionSlot
     * @throws \InvalidArgumentException if times are invalid
     */
    public function execute(AddSubscriptionSlotRequest $request): SubscriptionSlot
    {
        // Validate weekday
        if ($request->weekday < 1 || $request->weekday > 7) {
            throw new \InvalidArgumentException('Weekday must be between 1 (Monday) and 7 (Sunday).');
        }

        // Validate times
        if ($request->startTime >= $request->endTime) {
            throw new \InvalidArgumentException('Start time must be before end time.');
        }

        $slot = new SubscriptionSlot(
            0, // ID sera généré par la BDD
            $request->subscriptionTypeId,
            $request->weekday,
            $request->startTime,
            $request->endTime
        );

        return $this->slotRepository->save($slot);
    }
}
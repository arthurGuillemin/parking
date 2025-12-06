<?php

namespace App\Application\UseCase\Owner\AddSubscriptionSlot;

use App\Domain\Entity\SubscriptionSlot;
use App\Domain\Repository\SubscriptionSlotRepositoryInterface;
use App\Application\UseCase\Owner\AddSubscriptionSlot\AddSubscriptionSlotResponse;

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
     * @return AddSubscriptionSlotResponse
     * @throws \InvalidArgumentException if times are invalid
     */
    public function execute(AddSubscriptionSlotRequest $request): AddSubscriptionSlotResponse
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

        $savedSlot = $this->slotRepository->save($slot);

        return new AddSubscriptionSlotResponse(
            $savedSlot->getSubscriptionSlotId(),
            $savedSlot->getSubscriptionTypeId(),
            $savedSlot->getWeekday(),
            $savedSlot->getStartTime()->format('H:i:s'),
            $savedSlot->getEndTime()->format('H:i:s')
        );
    }
}
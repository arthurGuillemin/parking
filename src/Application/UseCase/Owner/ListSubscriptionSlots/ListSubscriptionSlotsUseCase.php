<?php

namespace App\Application\UseCase\Owner\ListSubscriptionSlots;

use App\Domain\Repository\SubscriptionSlotRepositoryInterface;
use App\Application\UseCase\Owner\AddSubscriptionSlot\AddSubscriptionSlotResponse;

class ListSubscriptionSlotsUseCase
{
    private SubscriptionSlotRepositoryInterface $slotRepository;

    public function __construct(SubscriptionSlotRepositoryInterface $slotRepository)
    {
        $this->slotRepository = $slotRepository;
    }

    public function execute(ListSubscriptionSlotsRequest $request): array
    {
        $slots = $this->slotRepository->findBySubscriptionTypeId($request->typeId);

        return array_map(function ($slot) {
            return new AddSubscriptionSlotResponse(
                $slot->getSubscriptionSlotId(),
                $slot->getSubscriptionTypeId(),
                $slot->getWeekday(),
                $slot->getStartTime()->format('H:i:s'),
                $slot->getEndTime()->format('H:i:s')
            );
        }, $slots);
    }
}

<?php

namespace App\Application\UseCase\Owner\DeleteSubscriptionSlot;

use App\Domain\Repository\SubscriptionSlotRepositoryInterface;

class DeleteSubscriptionSlotUseCase
{
    private SubscriptionSlotRepositoryInterface $slotRepository;

    public function __construct(SubscriptionSlotRepositoryInterface $slotRepository)
    {
        $this->slotRepository = $slotRepository;
    }

    public function execute(DeleteSubscriptionSlotRequest $request): void
    {
        $slot = $this->slotRepository->findById($request->id);

        if (!$slot) {
            throw new \RuntimeException("Subscription slot not found.");
        }

        $this->slotRepository->delete($request->id);
    }
}

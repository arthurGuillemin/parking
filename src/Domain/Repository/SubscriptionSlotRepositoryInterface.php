<?php

namespace App\Domain\Repository;

use App\Domain\Entity\SubscriptionSlot;

interface SubscriptionSlotRepositoryInterface {
    public function findById(int $id): ?SubscriptionSlot;

    public function findBySubscriptionTypeId(int $typeId): array;

    public function save(SubscriptionSlot $slot): SubscriptionSlot;
}

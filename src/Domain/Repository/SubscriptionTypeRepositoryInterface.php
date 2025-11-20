<?php

namespace App\Domain\Repository;

use App\Domain\Entity\SubscriptionType;

interface SubscriptionTypeRepositoryInterface {
    public function findById(int $id): ?SubscriptionType;

    public function findAll(): array;

    public function save(SubscriptionType $type): SubscriptionType;
}

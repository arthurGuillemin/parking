<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Owner;

interface OwnerRepositoryInterface {
    public function findById(string $id): ?Owner;
    public function findByEmail(string $email): ?Owner;
    public function save(Owner $owner): Owner;
}

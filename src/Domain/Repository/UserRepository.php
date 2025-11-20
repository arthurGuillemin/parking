<?php

namespace App\Domain\Repository;

use App\Domain\Entity\User;

interface UserRepository
{
    public function findById(string $id): ?User;
    public function findByEmail(string $email): ?User;
    public function save(User $user): User;
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Domain\Security\PasswordHasherInterface;

class PasswordHasher implements PasswordHasherInterface
{
    public function hash(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_BCRYPT);
    }
}

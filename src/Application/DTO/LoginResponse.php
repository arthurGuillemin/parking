<?php

namespace App\Application\DTO;

readonly class LoginResponse
{
    public function __construct(
        public string $token,
        public string $refreshToken,
        public int $expiresIn,
        public ?string $role = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
    ) {
    }
}

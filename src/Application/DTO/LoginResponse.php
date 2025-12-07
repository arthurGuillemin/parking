<?php

namespace App\Application\DTO;

readonly class LoginResponse {
    public function __construct(
        public string $token,
        public int $expiresIn
    ) {}
}

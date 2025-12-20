<?php

namespace App\Domain\Auth;

interface TokenGeneratorInterface {
    // Crée un token (string) à partir d'un User
    public function generate(array $payload): string;

    // Récupère l'ID (ou l'email) depuis un token. Renvoie null si invalide.
    public function decode(string $token): ?array;

}

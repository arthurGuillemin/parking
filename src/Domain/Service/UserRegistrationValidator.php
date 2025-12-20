<?php

declare(strict_types=1);

namespace App\Domain\Service;

class UserRegistrationValidator
{
    /**
     * Valide les données d'inscription.
     *
     * @return string[] Liste de messages d'erreur. Tableau vide si tout est valide.
     */
    public function validate(string $email, string $password, string $firstName, string $lastName): array
    {
        $errors = [];

        if (trim($email) === '') {
            $errors[] = 'L\'email est obligatoire.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Le format de l\'email est invalide.';
        }

        if (strlen($password) < 8) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
        }

        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une lettre et un chiffre.';
        }

        if (trim($firstName) === '') {
            $errors[] = 'Le prénom est obligatoire.';
        }

        if (trim($lastName) === '') {
            $errors[] = 'Le nom de famille est obligatoire.';
        }

        return $errors;
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Security;

/**
 * Service de protection contre les attaques XSS
 */
class XssProtectionService
{
    /**
     * Nettoie une chaîne de caractères pour prévenir les attaques XSS
     * 
     * @param string $input La chaîne à nettoyer
     * @return string La chaîne nettoyée
     */
    public function sanitize(string $input): string
    {
        $cleaned = strip_tags($input);

        $cleaned = htmlspecialchars($cleaned, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim($cleaned);
    }

    /**
     * Nettoie un tableau de données
     * 
     * @param array $data Les données à nettoyer
     * @return array Les données nettoyées
     */
    public function sanitizeArray(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $sanitizedKey = $this->sanitize((string) $key);
            if (is_string($value)) {
                $sanitized[$sanitizedKey] = $this->sanitize($value);
            } elseif (is_array($value)) {
                $sanitized[$sanitizedKey] = $this->sanitizeArray($value);
            } else {
                $sanitized[$sanitizedKey] = $value;
            }
        }
        return $sanitized;
    }

    /**
     * Valide et nettoie un email
     * 
     * @param string $email L'email à valider et nettoyer
     * @return string|null L'email nettoyé ou null si invalide
     */
    public function sanitizeEmail(string $email): ?string
    {
        $cleaned = trim($email);
        $cleaned = filter_var($cleaned, FILTER_SANITIZE_EMAIL);

        if (!filter_var($cleaned, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return strtolower($cleaned);
    }

    /**
     * Nettoie les données pour l'affichage JSON (échappe uniquement les caractères dangereux)
     * 
     * @param mixed $data Les données à préparer pour JSON
     * @return mixed Les données préparées
     */
    public function prepareForJson($data)
    {
        if (is_string($data)) {
            // Pour JSON, on échappe déjà les caractères spéciaux, mais on supprime les balises HTML
            return strip_tags($data);
        }

        if (is_array($data)) {
            return array_map([$this, 'prepareForJson'], $data);
        }

        return $data;
    }
}


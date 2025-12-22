<?php

namespace App\Interface\Controller;

use App\Domain\Service\ParkingService;
use App\Domain\Service\JwtService;
use App\Domain\Security\XssProtectionService;
use Exception;

class ParkingController
{
    private ParkingService $parkingService;
    private JwtService $jwtService;
    private XssProtectionService $xssProtection;

    public function __construct(ParkingService $parkingService, JwtService $jwtService, XssProtectionService $xssProtection)
    {
        $this->parkingService = $parkingService;
        $this->jwtService = $jwtService;
        $this->xssProtection = $xssProtection;
    }

    /**
     * Ajoute un nouveau parking
     */
    public function add(array $data): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        try {
            $data = $this->mergeJsonInput($data);
            $data = $this->extractOwnerIdFromToken($data);
            $this->validateParkingFields($data);

            $parking = $this->createParking($data);
            echo $this->formatParkingResponse($parking);
        } catch (\Throwable $e) {
            $this->sendErrorResponse($e);
        }
    }

    /**
     * Met à jour un parking existant
     */
    public function update(array $params): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        try {
            $data = $this->parseJsonBody();
            $data['id'] = $data['id'] ?? $params['id'] ?? null;

            if (empty($data['id'])) {
                throw new \InvalidArgumentException('ID du parking manquant');
            }

            $data = $this->sanitizeParkingData($data);
            $parking = $this->parkingService->updateParking((int) $data['id'], $data);

            echo json_encode([
                'id' => $parking->getParkingId(),
                'name' => $parking->getName(),
                'open_24_7' => $parking->isOpen24_7(),
                'message' => 'Parking mis à jour'
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    public function addForm(): void
    {
        require dirname(__DIR__, 3) . '/templates/parking_add.php';
    }

    public function list(): void
    {
        $lat = $_GET['lat'] ?? null;
        $lng = $_GET['lng'] ?? null;

        $parkings = ($lat && $lng)
            ? $this->parkingService->searchNearby((float) $lat, (float) $lng)
            : $this->parkingService->getAllParkings();

        require dirname(__DIR__, 3) . '/templates/parking_list_user.php';
    }

    /**
     * Liste les parkings du propriétaire connecté
     */
    public function listOwnedParkings(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        $ownerId = $this->getAuthenticatedUserId();
        if (!$ownerId) {
            http_response_code(401);
            echo json_encode(['error' => 'Non authentifié'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $parkings = $this->parkingService->getParkingsByOwner($ownerId);
        $data = array_map([$this, 'formatParkingToArray'], $parkings);

        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function manage(array $params): void
    {
        $parkingId = $params['id'] ?? null;
        if (!$parkingId) {
            http_response_code(404);
            echo "Parking not found";
            return;
        }

        $parking = $this->parkingService->getParkingById((int) $parkingId);
        if (!$parking) {
            http_response_code(404);
            echo "Parking not found";
            return;
        }

        $this->checkAuth();
        require dirname(__DIR__, 3) . '/templates/parking_manage.php';
    }

    /**
     * Fusionne les données JSON avec les données existantes
     */
    private function mergeJsonInput(array $data): array
    {
        if (empty($data['name'])) {
            $jsonData = $this->parseJsonBody();
            $data = array_merge($data, $jsonData);
        }
        return $data;
    }

    /**
     * Parse le body JSON de la requête
     */
    private function parseJsonBody(): array
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }

    /**
     * Extrait l'ID propriétaire du token JWT si non fourni
     */
    private function extractOwnerIdFromToken(array $data): array
    {
        if (empty($data['ownerId']) && isset($_COOKIE['auth_token'])) {
            $payload = $this->jwtService->decode($_COOKIE['auth_token']);
            if ($payload) {
                $data['ownerId'] = $payload['user_id'] ?? null;
            }
        }
        return $data;
    }

    /**
     * Récupère l'ID utilisateur authentifié depuis le token
     */
    private function getAuthenticatedUserId(): ?string
    {
        if (!isset($_COOKIE['auth_token'])) {
            return null;
        }
        $payload = $this->jwtService->decode($_COOKIE['auth_token']);
        return $payload['user_id'] ?? null;
    }

    /**
     * Valide les champs requis pour un parking
     */
    private function validateParkingFields(array $data): void
    {
        $requiredFields = ['ownerId', 'name', 'address'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Champs requis manquants: $field");
            }
        }
        if (!isset($data['latitude'])) {
            throw new \InvalidArgumentException('Champs requis manquants: latitude');
        }
        if (!isset($data['longitude'])) {
            throw new \InvalidArgumentException('Champs requis manquants: longitude');
        }
        if (isset($data['totalCapacity']) && $data['totalCapacity'] === '') {
            throw new \InvalidArgumentException('Champs requis manquants: totalCapacity');
        }
    }

    /**
     * Applique le sanitize XSS sur les données du parking
     */
    private function sanitizeParkingData(array $data): array
    {
        if (isset($data['name'])) {
            $data['name'] = $this->xssProtection->sanitize($data['name']);
        }
        if (isset($data['address'])) {
            $data['address'] = $this->xssProtection->sanitize($data['address']);
        }
        return $data;
    }

    /**
     * Crée un parking avec les données fournies
     */
    private function createParking(array $data)
    {
        $name = $this->xssProtection->sanitize($data['name']);
        $address = $this->xssProtection->sanitize($data['address']);
        $open24_7 = isset($data['open_24_7']) ? (bool) $data['open_24_7'] : false;

        return $this->parkingService->addParking(
            $data['ownerId'],
            $name,
            $address,
            (float) $data['latitude'],
            (float) $data['longitude'],
            (int) $data['totalCapacity'],
            $open24_7
        );
    }

    /**
     * Formate un parking en réponse JSON
     */
    private function formatParkingResponse($parking): string
    {
        return json_encode([
            'id' => $parking->getParkingId(),
            'ownerId' => $parking->getOwnerId(),
            'name' => $parking->getName(),
            'address' => $parking->getAddress(),
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Formate un parking en tableau pour listOwnedParkings
     */
    private function formatParkingToArray($parking): array
    {
        return [
            'id' => $parking->getParkingId(),
            'ownerId' => $parking->getOwnerId(),
            'name' => $parking->getName(),
            'address' => $parking->getAddress(),
            'latitude' => $parking->getLatitude(),
            'longitude' => $parking->getLongitude(),
            'totalCapacity' => $parking->getTotalCapacity(),
            'open_24_7' => $parking->isOpen24_7(),
        ];
    }

    /**
     * Envoie une réponse d'erreur JSON
     */
    private function sendErrorResponse(\Throwable $e): void
    {
        error_log('Error adding parking: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        http_response_code(500);
        echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }

    private function checkAuth(): void
    {
        if (!isset($_COOKIE['auth_token']) || !$this->jwtService->validateToken($_COOKIE['auth_token'])) {
            header('Location: /login');
            exit;
        }
    }
}

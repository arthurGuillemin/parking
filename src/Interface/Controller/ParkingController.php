<?php

namespace App\Interface\Controller;

use App\Domain\Service\ParkingService;
use App\Domain\Service\JwtService;
use Exception;

class ParkingController
{
    private ParkingService $parkingService;
    private JwtService $jwtService;

    public function __construct(ParkingService $parkingService, JwtService $jwtService)
    {
        $this->parkingService = $parkingService;
        $this->jwtService = $jwtService;
    }

    public function add(array $data): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        try {
            // Handle JSON body if $data from params/POST is insufficient
            if (empty($data['name'])) {
                $input = file_get_contents('php://input');
                $jsonData = json_decode($input, true);
                if (is_array($jsonData)) {
                    $data = array_merge($data, $jsonData);
                }
            }

            // Attempt to get ownerId from cookie if missing
            if (empty($data['ownerId']) && isset($_COOKIE['auth_token'])) {
                $payload = $this->jwtService->decode($_COOKIE['auth_token']);
                if ($payload) {
                    $data['ownerId'] = $payload['user_id'] ?? null;
                }
            }

            // Validation: Ensure all required fields are present and not empty
            // Note: latitude/longitude/totalCapacity can be 0, so use isset or logic that accepts 0.
            // But usually empty(0) is true. So we should check !isset for numeric fields or strict empty check.
            if (empty($data['ownerId'])) {
                throw new \InvalidArgumentException('Champs requis manquants: ownerId');
            }
            if (empty($data['name'])) {
                throw new \InvalidArgumentException('Champs requis manquants: name');
            }
            if (empty($data['address'])) {
                throw new \InvalidArgumentException('Champs requis manquants: address');
            }
            if (!isset($data['latitude'])) {
                throw new \InvalidArgumentException('Champs requis manquants: latitude');
            }
            if (!isset($data['longitude'])) {
                throw new \InvalidArgumentException('Champs requis manquants: longitude');
            }
            if (!isset($data['totalCapacity'])) {
                throw new \InvalidArgumentException('Champs requis manquants: totalCapacity');
            }

            $open_24_7 = isset($data['open_24_7']) ? (bool) $data['open_24_7'] : false;
            $parking = $this->parkingService->addParking(
                $data['ownerId'],
                $data['name'],
                $data['address'],
                (float) $data['latitude'],
                (float) $data['longitude'],
                (int) $data['totalCapacity'],
                $open_24_7
            );

            echo json_encode([
                'id' => $parking->getParkingId(),
                'ownerId' => $parking->getOwnerId(),
                'name' => $parking->getName(),
                'address' => $parking->getAddress(),
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) { // Catch both Exception and Error
            error_log('Error adding parking: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            http_response_code(500); // Internal Server Error is more appropriate for unexpected errors
            echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    public function update(array $params): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true) ?? [];

            // Merge with params if needed (though usually POST body is enough)
            if (empty($data['id']) && !empty($params['id'])) {
                $data['id'] = $params['id'];
            }

            if (empty($data['id'])) {
                throw new \InvalidArgumentException('ID du parking manquant');
            }

            // TODO: check owner permissions

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
        // For now, no args or simple query handling.
        // We fetch ALL parkings. Search logic can be added later (filtering array or SQL).
        $parkings = $this->parkingService->getAllParkings();

        // Pass data to view
        // $parkings is available in the included file.
        require dirname(__DIR__, 3) . '/templates/parking_list_user.php';
    }

    public function listOwnedParkings(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        $ownerId = null;
        if (isset($_COOKIE['auth_token'])) {
            $payload = $this->jwtService->decode($_COOKIE['auth_token']);
            if ($payload) {
                $ownerId = $payload['user_id'] ?? null;
            }
        }

        if (!$ownerId) {
            http_response_code(401);
            echo json_encode(['error' => 'Non authentifié'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $parkings = $this->parkingService->getParkingsByOwner($ownerId);

        $data = array_map(function ($parking) {
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
        }, $parkings);

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

        // Verify owner owns this parking... (TODO: Add check using session/token)

        require dirname(__DIR__, 3) . '/templates/parking_manage.php';
    }
}

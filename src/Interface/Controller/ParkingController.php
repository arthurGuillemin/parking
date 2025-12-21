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

    public function add(array $data): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        try {
            if (empty($data['name'])) {
                $input = file_get_contents('php://input');
                $jsonData = json_decode($input, true);
                if (is_array($jsonData)) {
                    $data = array_merge($data, $jsonData);
                }
            }

            if (empty($data['ownerId']) && isset($_COOKIE['auth_token'])) {
                $payload = $this->jwtService->decode($_COOKIE['auth_token']);
                if ($payload) {
                    $data['ownerId'] = $payload['user_id'] ?? null;
                }
            }

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
            if (isset($data['totalCapacity']) && $data['totalCapacity'] === '') {
                throw new \InvalidArgumentException('Champs requis manquants: totalCapacity');
            }


            $name = $this->xssProtection->sanitize($data['name']);
            $address = $this->xssProtection->sanitize($data['address']);


            $open_24_7 = isset($data['open_24_7']) ? (bool) $data['open_24_7'] : false;
            $parking = $this->parkingService->addParking(
                $data['ownerId'],
                $name,
                $address,
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
        } catch (\Throwable $e) {
            error_log('Error adding parking: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    public function update(array $params): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true) ?? [];

            if (empty($data['id']) && !empty($params['id'])) {
                $data['id'] = $params['id'];
            }

            if (empty($data['id'])) {
                throw new \InvalidArgumentException('ID du parking manquant');
            }

            // Sanitize
            if (isset($data['name'])) {
                $data['name'] = $this->xssProtection->sanitize($data['name']);
            }
            if (isset($data['address'])) {
                $data['address'] = $this->xssProtection->sanitize($data['address']);
            }

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

        if ($lat && $lng) {
            $parkings = $this->parkingService->searchNearby((float) $lat, (float) $lng);
        } else {
            $parkings = $this->parkingService->getAllParkings();
        }

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

        $this->checkAuth();

        require dirname(__DIR__, 3) . '/templates/parking_manage.php';
    }

    private function checkAuth(): void
    {
        if (!isset($_COOKIE['auth_token']) || !$this->jwtService->validateToken($_COOKIE['auth_token'])) {
            header('Location: /login');
            exit;
        }
    }
}

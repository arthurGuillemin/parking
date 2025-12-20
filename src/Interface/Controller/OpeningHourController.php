<?php

namespace App\Interface\Controller;

use App\Domain\Service\OpeningHourService;
use App\Application\UseCase\Owner\UpdateOpeningHour\UpdateOpeningHourRequest;
use Exception;

class OpeningHourController
{
    private OpeningHourService $openingHourService;

    public function __construct(OpeningHourService $openingHourService)
    {
        $this->openingHourService = $openingHourService;
    }

    public function add(array $data): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        // Handle JSON body
        if (empty($data['parkingId'])) {
            $input = file_get_contents('php://input');
            $jsonData = json_decode($input, true);
            if (is_array($jsonData)) {
                $data = array_merge($data, $jsonData);
            }
        }

        try {
            if (empty($data['parkingId']) || !isset($data['weekdayStart']) || !isset($data['weekdayEnd']) || empty($data['openingTime']) || empty($data['closingTime'])) {
                throw new \InvalidArgumentException('Champs requis manquants pour l\'ajout');
            }

            $openingHour = $this->openingHourService->addOpeningHour(
                (int) $data['parkingId'],
                (int) $data['weekdayStart'],
                (int) $data['weekdayEnd'],
                $data['openingTime'],
                $data['closingTime']
            );

            echo json_encode([
                'id' => $openingHour->getOpeningHourId(),
                'parkingId' => $openingHour->getParkingId(),
                'message' => 'Horaire ajouté'
            ], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    public function delete(array $params): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        try {
            $id = $params['id'] ?? null;
            if (!$id) {
                // Try json body
                $input = file_get_contents('php://input');
                $data = json_decode($input, true);
                $id = $data['id'] ?? null;
            }

            if (!$id) {
                throw new \InvalidArgumentException('ID manquant');
            }

            $this->openingHourService->deleteOpeningHour((int) $id);
            echo json_encode(['message' => 'Horaire supprimé'], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    public function list(array $data): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        $parkingId = $data['parkingId'] ?? $_GET['parkingId'] ?? null;

        if (!$parkingId) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing parkingId']);
            return;
        }

        $hours = $this->openingHourService->getOpeningHoursByParkingId((int) $parkingId);

        $output = array_map(function ($hour) {
            return [
                'id' => $hour->getOpeningHourId(),
                'parkingId' => $hour->getParkingId(),
                'weekdayStart' => $hour->getWeekdayStart(),
                'weekdayEnd' => $hour->getWeekdayEnd(),
                'openingTime' => $hour->getOpeningTime()->format('H:i'),
                'closingTime' => $hour->getClosingTime()->format('H:i'),
            ];
        }, $hours);

        echo json_encode($output);
    }
}

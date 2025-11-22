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

    public function update(array $data): array
    {
        if (empty($data['parkingId']) || !isset($data['weekday']) || empty($data['openingTime']) || empty($data['closingTime'])) {
            throw new Exception('Champs requis manquants');
        }
        $request = new UpdateOpeningHourRequest(
            (int)$data['parkingId'],
            (int)$data['weekday'],
            $data['openingTime'],
            $data['closingTime']
        );
        $openingHour = $this->openingHourService->updateOpeningHour($request);
        return [
            'id' => $openingHour->getOpeningHourId(),
            'parkingId' => $openingHour->getParkingId(),
            'weekday' => $openingHour->getWeekday(),
            'openingTime' => $openingHour->getOpeningTime()->format('H:i:s'),
            'closingTime' => $openingHour->getClosingTime()->format('H:i:s'),
        ];
    }
}


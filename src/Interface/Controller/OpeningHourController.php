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
        if (empty($data['parkingId']) || !isset($data['weekdayStart']) || !isset($data['weekdayEnd']) || empty($data['openingTime']) || empty($data['closingTime'])) {
            throw new \InvalidArgumentException('Champs requis manquants');
        }
        $request = new UpdateOpeningHourRequest(
            (int)$data['parkingId'],
            (int)$data['weekdayStart'],
            (int)$data['weekdayEnd'],
            $data['openingTime'],
            $data['closingTime']
        );
        $openingHour = $this->openingHourService->updateOpeningHour($request);
        return [
            'id' => $openingHour->getOpeningHourId(),
            'parkingId' => $openingHour->getParkingId(),
            'weekdayStart' => $openingHour->getWeekdayStart(),
            'weekdayEnd' => $openingHour->getWeekdayEnd(),
            'openingTime' => $openingHour->getOpeningTime()->format('H:i:s'),
            'closingTime' => $openingHour->getClosingTime()->format('H:i:s'),
        ];
    }
}

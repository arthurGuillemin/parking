<?php

namespace App\Interface\Controller;

use App\Application\UseCase\User\EnterParking\EnterParkingUseCase;
use App\Application\UseCase\User\EnterParking\EnterParkingRequest;
use App\Application\UseCase\User\ExitParking\ExitParkingUseCase;
use App\Application\UseCase\User\ExitParking\ExitParkingRequest;
use Exception;

class ParkingEntryExitController
{
    private EnterParkingUseCase $enterParkingUseCase;
    private ExitParkingUseCase $exitParkingUseCase;

    public function __construct(EnterParkingUseCase $enterParkingUseCase, ExitParkingUseCase $exitParkingUseCase)
    {
        $this->enterParkingUseCase = $enterParkingUseCase;
        $this->exitParkingUseCase = $exitParkingUseCase;
    }

    public function enter(array $data): array
    {
        if (empty($data['userId']) || empty($data['parkingId'])) {
            throw new \InvalidArgumentException('Missing userId or parkingId.');
        }

        $request = new EnterParkingRequest($data['userId'], (int) $data['parkingId']);
        $response = $this->enterParkingUseCase->execute($request);

        return [
            'sessionId' => $response->id,
            'entryDateTime' => $response->entryDateTime,
            'message' => 'Entrée validée.'
        ];
    }

    public function exit(array $data): array
    {
        if (empty($data['userId']) || empty($data['parkingId'])) {
            throw new \InvalidArgumentException('Missing userId or parkingId.');
        }

        $request = new ExitParkingRequest($data['userId'], (int) $data['parkingId']);
        $response = $this->exitParkingUseCase->execute($request);

        return [
            'sessionId' => $response->id,
            'exitDateTime' => $response->exitDateTime,
            'amount' => $response->amount,
            'message' => 'Sortie validée. Paiement effectué.'
        ];
    }
}

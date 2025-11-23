<?php

namespace App\Application\UseCase\Owner\GetAvailableSpots;

use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Repository\ParkingSessionRepositoryInterface;

class GetAvailableSpotsUseCase
{
    private ParkingRepositoryInterface $parkingRepository;
    private ParkingSessionRepositoryInterface $parkingSessionRepository;

    public function __construct(ParkingRepositoryInterface $parkingRepository, ParkingSessionRepositoryInterface $parkingSessionRepository)
    {
        $this->parkingRepository = $parkingRepository;
        $this->parkingSessionRepository = $parkingSessionRepository;
    }

    /**
     * Get the number of available spots in a parking at a given timestamp.
     *
     * @param GetAvailableSpotsRequest $request
     * @return int
     */
    public function execute(GetAvailableSpotsRequest $request): int
    {
        $parking = $this->parkingRepository->findById($request->parkingId);
        if (!$parking) {
            throw new \InvalidArgumentException('Parking non trouvé.');
        }
        $sessions = $this->parkingSessionRepository->findByParkingId($request->parkingId);
        $occupied = 0;
        foreach ($sessions as $session) {
            $entry = $session->getEntryDateTime();
            $exit = $session->getExitDateTime();
            if ($entry <= $request->at && (is_null($exit) || $exit > $request->at)) {
                $occupied++;
            }
        }
        return max(0, $parking->getTotalCapacity() - $occupied);
    }
}


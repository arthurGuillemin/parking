<?php

namespace App\Domain\Service;

use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Application\UseCase\Owner\GetAvailableSpots\GetAvailableSpotsUseCase;
use App\Application\UseCase\Owner\GetAvailableSpots\GetAvailableSpotsRequest;
use App\Domain\Repository\OpeningHourRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Domain\Entity\Parking;

class ParkingAvailabilityService
{
    private GetAvailableSpotsUseCase $getAvailableSpotsUseCase;
    private OpeningHourRepositoryInterface $openingHourRepository;

    public function __construct(
        ParkingRepositoryInterface $parkingRepository,
        ParkingSessionRepositoryInterface $parkingSessionRepository,
        OpeningHourRepositoryInterface $openingHourRepository,
        ReservationRepositoryInterface $reservationRepository,
        SubscriptionRepositoryInterface $subscriptionRepository
    ) {
        $this->getAvailableSpotsUseCase = new GetAvailableSpotsUseCase(
            $parkingRepository,
            $parkingSessionRepository,
            $reservationRepository,
            $subscriptionRepository
        );
        $this->openingHourRepository = $openingHourRepository;
    }

    public function getAvailableSpots(GetAvailableSpotsRequest $request): int
    {
        return $this->getAvailableSpotsUseCase->execute($request);
    }

    public function isAvailable(Parking $parking, \DateTimeImmutable $dateTime): bool
    {
        if ($parking->isOpen24_7()) {
            return $this->hasAvailableSpots($parking, $dateTime);
        }
        if (!$this->isOpenAt($parking, $dateTime)) {
            return false;
        }
        return $this->hasAvailableSpots($parking, $dateTime);
    }

    private function hasAvailableSpots(Parking $parking, \DateTimeImmutable $dateTime): bool
    {
        return $this->getAvailableSpots(new GetAvailableSpotsRequest($parking->getParkingId(), $dateTime)) > 0;
    }

    private function isOpenAt(Parking $parking, \DateTimeImmutable $dateTime): bool
    {
        $openingHours = $this->openingHourRepository->findByParkingId($parking->getParkingId());
        $weekday = (int)$dateTime->format('N');
        $time = $dateTime->format('H:i:s');
        foreach ($openingHours as $openingHour) {
            if ($weekday >= $openingHour->getWeekdayStart() && $weekday <= $openingHour->getWeekdayEnd()) {
                $opening = $openingHour->getOpeningTime()->format('H:i:s');
                $closing = $openingHour->getClosingTime()->format('H:i:s');
                if ($opening < $closing) {
                    if ($time >= $opening && $time < $closing) {
                        return true;
                    }
                } else {
                    if ($time >= $opening || $time < $closing) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}

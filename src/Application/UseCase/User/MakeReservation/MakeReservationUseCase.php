<?php

namespace App\Application\UseCase\User\MakeReservation;

use App\Application\DTO\Response\ReservationResponse;
use App\Domain\Entity\Reservation;
use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Service\CheckAvailabilityService;
use App\Domain\Repository\PricingRuleRepositoryInterface;

class MakeReservationUseCase
{
    private ReservationRepositoryInterface $reservationRepository;
    private ParkingRepositoryInterface $parkingRepository;
    private CheckAvailabilityService $checkAvailabilityService;
    private \App\Domain\Service\PricingService $pricingService;

    public function __construct(
        ReservationRepositoryInterface $reservationRepository,
        ParkingRepositoryInterface $parkingRepository,
        CheckAvailabilityService $checkAvailabilityService,
        \App\Domain\Service\PricingService $pricingService
    ) {
        $this->reservationRepository = $reservationRepository;
        $this->parkingRepository = $parkingRepository;
        $this->checkAvailabilityService = $checkAvailabilityService;
        $this->pricingService = $pricingService;
    }

    public function execute(MakeReservationRequest $request): ReservationResponse
    {
        $parking = $this->parkingRepository->findById($request->parkingId);
        if (!$parking) {
            throw new \Exception("Parking not found");
        }

        // vérifier si le parking est disponible
        if (!$this->checkAvailabilityService->checkAvailability($parking, $request->startDateTime, $request->endDateTime)) {
            throw new \Exception("Parking is full during this period.");
        }

        // calculer le prix via PricingService (supporte les tiers et l'historique)
        $duration = $request->startDateTime->diff($request->endDateTime);
        $amount = $this->pricingService->calculatePrice(
            $parking->getParkingId(),
            $duration,
            $request->startDateTime // Les règles effectives au début de la résa
        );

        // créer la réservation
        $reservation = new Reservation(
            0,
            $request->userId,
            $request->parkingId,
            $request->startDateTime,
            $request->endDateTime,
            'pending',
            $amount,
            null
        );

        $savedReservation = $this->reservationRepository->save($reservation);

        return new ReservationResponse($savedReservation);
    }
}

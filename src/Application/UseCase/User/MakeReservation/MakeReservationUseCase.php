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
    private PricingRuleRepositoryInterface $pricingRuleRepository;

    public function __construct(
        ReservationRepositoryInterface $reservationRepository,
        ParkingRepositoryInterface $parkingRepository,
        CheckAvailabilityService $checkAvailabilityService,
        PricingRuleRepositoryInterface $pricingRuleRepository
    ) {
        $this->reservationRepository = $reservationRepository;
        $this->parkingRepository = $parkingRepository;
        $this->checkAvailabilityService = $checkAvailabilityService;
        $this->pricingRuleRepository = $pricingRuleRepository;
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

        // calculer le prix
        $rule = $this->pricingRuleRepository->findApplicableRule($parking->getParkingId(), $request->startDateTime);
        $amount = 0.0;

        if ($rule) {
            $durationMinutes = ($request->endDateTime->getTimestamp() - $request->startDateTime->getTimestamp()) / 60;
            $slices = ceil($durationMinutes / $rule->getSliceInMinutes());
            $amount = $slices * $rule->getPricePerSlice();
        } else {
            $amount = 0.0;
        }

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

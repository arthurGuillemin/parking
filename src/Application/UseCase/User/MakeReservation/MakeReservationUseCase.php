<?php

namespace App\Application\UseCase\User\MakeReservation;

use App\Application\DTO\Response\ReservationResponse;
use App\Domain\Entity\Reservation;
use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Service\CheckAvailabilityService;
use App\Domain\Repository\PricingRuleRepositoryInterface; // Optional usage

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

        // 1. Check Availability
        if (!$this->checkAvailabilityService->checkAvailability($parking, $request->startDateTime, $request->endDateTime)) {
            throw new \Exception("Parking is full during this period.");
        }

        // 2. Calculate Price
        // Find applicable rule for start time
        $rule = $this->pricingRuleRepository->findApplicableRule($parking->getParkingId(), $request->startDateTime);
        $amount = 0.0;

        if ($rule) {
            $durationMinutes = ($request->endDateTime->getTimestamp() - $request->startDateTime->getTimestamp()) / 60;
            $slices = ceil($durationMinutes / $rule->getSliceInMinutes());
            $amount = $slices * $rule->getPricePerSlice();
        } else {
            // Fallback or Exception. For now default to 0.0 or throw?
            // Assuming free if no rule or just 0 for MVP
        }

        // 3. Create Reservation
        // ID is random int for now (or auto-increment handled by repo/db), typically 0 if repo generates it.
        $reservation = new Reservation(
            0,
            $request->userId,
            $request->parkingId,
            $request->startDateTime,
            $request->endDateTime,
            'pending', // Status
            $amount,
            null // finalAmount is null until updated? Or should match calculated? Requirements say "facturé sur la totalité".
        );
        // Requirement: "Un utilisateur ... se voit quand même facturé sur la totalité".
        // Use calculatedAmount as the reference.

        $savedReservation = $this->reservationRepository->save($reservation);

        return new ReservationResponse($savedReservation);
    }
}

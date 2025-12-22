<?php

namespace App\Application\UseCase\User\ExitParking;

use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Repository\InvoiceRepositoryInterface;
use App\Domain\Entity\ParkingSession;
use App\Domain\Entity\Invoice;
use App\Application\DTO\Response\ParkingSessionResponse;
use App\Domain\Service\PricingService;
use DateTimeImmutable;
use RuntimeException;

class ExitParkingUseCase
{
    private ParkingSessionRepositoryInterface $sessionRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private InvoiceRepositoryInterface $invoiceRepository;
    private PricingService $pricingService;

    public function __construct(
        ParkingSessionRepositoryInterface $sessionRepository,
        ReservationRepositoryInterface $reservationRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        PricingService $pricingService
    ) {
        $this->sessionRepository = $sessionRepository;
        $this->reservationRepository = $reservationRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->pricingService = $pricingService;
    }

    public function execute(ExitParkingRequest $request): ParkingSessionResponse
    {
        // 1. Trouver la session active
        $session = $this->sessionRepository->findActiveSessionByUserId($request->userId);
        if (!$session) {
            throw new RuntimeException("Aucun stationnement actif trouvé.");
        }
        if ($session->getParkingId() !== $request->parkingId) {
            throw new RuntimeException("Vous n'êtes pas stationné dans ce parking.");
        }

        // 2. Définir l'heure de sortie
        $exitTime = new DateTimeImmutable();
        $session->setExitDateTime($exitTime);

        // 3. Calculer le montant final / la pénalité
        $finalAmount = 0.0;
        $isPenalty = false;
        $reservationId = $session->getReservationId();

        if ($reservationId) {
            $reservation = $this->reservationRepository->findById($reservationId);
            if ($reservation) {
                if ($exitTime > $reservation->getEndDateTime()) {
                    
                    $isPenalty = true;
                    $penaltyAmount = 20.0;

                    // Calculer le prix basé sur la durée effective (Entry -> Exit)
                    // Règle : "si vous avez reserver pour 3h et que vous êtes resté 4h, vous devez payer le prix de 4h"

                    $actualDuration = $session->getEntryDateTime()->diff($exitTime);

                    // Utiliser PricingService (vérifie les règles actives au moment de l'entrée)
                    $basePrice = $this->pricingService->calculatePrice(
                        $request->parkingId,
                        $actualDuration,
                        $session->getEntryDateTime()
                    );

                    $finalAmount = $basePrice + $penaltyAmount;

                } else {
                    $finalAmount = $reservation->getCalculatedAmount();
                }
            }
        } else {
            $finalAmount = 0.0;
        }

        $session->setFinalAmount($finalAmount);
        $session->setPenaltyApplied($isPenalty);

        $savedSession = $this->sessionRepository->save($session);

        if ($reservationId && isset($reservation)) {
            // Marquer la réservation comme terminée pour libérer le parking immédiatement
            // Cela met à jour endDateTime à exitTime et status à 'completed'
            $reservation->complete($exitTime, $finalAmount);
            $this->reservationRepository->save($reservation);
        }

        // 4. Générer une facture
        $details = [
            'penalty_applied' => $isPenalty,
            'duration_minutes' => ($exitTime->getTimestamp() - $session->getEntryDateTime()->getTimestamp()) / 60
        ];

        $invoice = new Invoice(
            0,
            $reservationId,
            $savedSession->getSessionId(),
            new DateTimeImmutable(),
            $finalAmount / 1.20, // HT approx
            $finalAmount,
            json_encode($details),
            $reservationId ? 'reservation' : 'session'
        );

        $this->invoiceRepository->save($invoice);

        return new ParkingSessionResponse($savedSession);
    }
}

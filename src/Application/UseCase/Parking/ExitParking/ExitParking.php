<?php

declare(strict_types=1);

namespace App\Application\UseCase\Parking;

use App\Domain\Penalty\AuthorizedRangeProviderInterface;
use App\Domain\Penalty\ParkingOverstayService;
use App\Domain\Pricing\ParkingPricingProviderInterface;
use App\Domain\Pricing\ReservationPriceCalculator;
use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Domain\ValueObject\TimeRange;
use DateTimeImmutable;
use RuntimeException;

final class ExitParking
{
    public function __construct(
        private ParkingSessionRepositoryInterface $parkingSessionRepository,
        private AuthorizedRangeProviderInterface $authorizedRangeProvider,
        private ParkingPricingProviderInterface $pricingProvider,
        private ReservationPriceCalculator $priceCalculator,
        private ParkingOverstayService $overstayService
    ) {}

    public function execute(int $sessionId, ?DateTimeImmutable $exitDateTime = null): ExitParkingResult
    {
        // 1. Récupérer la session
        $session = $this->parkingSessionRepository->findById($sessionId);

        if (!$session) {
            throw new RuntimeException(sprintf('Session de parking %d introuvable', $sessionId));
        }

        // 2. Vérifier qu'elle est encore active
        if ($session->getExitDateTime() !== null) {
            throw new RuntimeException('La session de parking est déjà clôturée');
        }

        // 3. Date de sortie effective
        $exitDateTime ??= new DateTimeImmutable();

        // 4. Créneau réel du stationnement (entry -> exit)
        $actualRange = new TimeRange(
            $session->getEntryDateTime(),
            $exitDateTime
        );

        // 5. Créneaux autorisés (réservation + abonnements)
        $authorizedRanges = $this->authorizedRangeProvider->getAuthorizedRangesForSession($session);

        if (empty($authorizedRanges)) {
            throw new RuntimeException('Aucun créneau autorisé trouvé pour cette session');
        }

        // Pour le prix de base on se base sur la réservation principale 
        $reservationRange = $authorizedRanges[0];

        // Récupérer la grille tarifaire (TariffSlot[]) du parking
        $tariffSlots = $this->pricingProvider->getPricingSlotsForParking($session->getParkingId());
        if (empty($tariffSlots)) {
            throw new RuntimeException('Aucune règle tarifaire disponible pour ce parking');
        }

        // Calcul du prix de base de la réservation
        $basePriceCents = $this->priceCalculator->calculate($reservationRange, $tariffSlots);

        // Calcul du dépassement et de la pénalité
        $overstayResult = $this->overstayService->evaluateOverstay(
            $session,
            $authorizedRanges,
            $exitDateTime
        );

        $penaltyCents = $overstayResult->getPenaltyAmountCents();
        $totalCents   = $basePriceCents + $penaltyCents;

        // Clôturer la session 
        $updatedSession = $session->withExit(
            exitDateTime: $exitDateTime,
            finalAmount: $totalCents / 100.0,          // on stocke en euros en float
            penaltyApplied: $overstayResult->hasOverstay()
        );

        // Sauvegarder en BDD
        $this->parkingSessionRepository->save($updatedSession);

        // Retourner le résultat métier
        return new ExitParkingResult(
            sessionId: $updatedSession->getSessionId(),
            basePriceCents: $basePriceCents,
            penaltyCents: $penaltyCents,
            totalCents: $totalCents
        );
    }
}

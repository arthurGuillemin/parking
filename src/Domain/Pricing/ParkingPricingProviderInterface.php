<?php

declare(strict_types=1);

namespace App\Domain\Pricing;

/**
 * Fournit la grille tarifaire d'un parking sous forme de TariffSlot[],
 * à partir des entités de tarification (PricingRule, etc.).
 */
interface ParkingPricingProviderInterface
{
    /**
     * @return TariffSlot[]
     */
    public function getPricingSlotsForParking(int $parkingId): array;
}

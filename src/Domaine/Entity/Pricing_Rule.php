<?php

namespace App\Domaine\Entity;

class Pricing_Rule {
    private int $id;
    private int $parkingId;
    private int $startDurationMinute;
    private ?int $endDurationMinute; // null si illimité
    private float $pricePerSlice;
    private int $sliceInMinutes; // 15 minutes
    private \DateTimeImmutable $effectiveDate; // pour l'historisation

    public function __construct(int $id, int $parkingId, int $startDurationMinute, ?int $endDurationMinute, float $pricePerSlice, int $sliceInMinutes, \DateTimeImmutable $effectiveDate) {
        $this->id = $id;
        $this->parkingId = $parkingId;
        $this->startDurationMinute = $startDurationMinute;
        $this->endDurationMinute = $endDurationMinute;
        $this->pricePerSlice = $pricePerSlice;
        $this->sliceInMinutes = $sliceInMinutes;
        $this->effectiveDate = $effectiveDate;
    }

    public function getPricingRuleId(): int {
        return $this->id;
    }

    public function getParkingId(): int {
        return $this->parkingId;
    }

    public function getStartDurationMinute(): int {
        return $this->startDurationMinute;
    }

    public function getEndDurationMinute(): ?int {
        return $this->endDurationMinute;
    }

    public function getPricePerSlice(): float {
        return $this->pricePerSlice;
    }

    public function getSliceInMinutes(): int {
        return $this->sliceInMinutes;
    }

    public function getEffectiveDate(): \DateTimeImmutable {
        return $this->effectiveDate;
    }
}

<?php

namespace App\Application\UseCase\Owner\UpdatePricingRule;

class UpdatePricingRuleRequest
{
    public int $parkingId;
    public int $startDurationMinute;
    public ?int $endDurationMinute;
    public float $pricePerSlice;
    public int $sliceInMinutes;
    public \DateTimeImmutable $effectiveDate;

    public function __construct(
        int $parkingId,
        int $startDurationMinute,
        ?int $endDurationMinute,
        float $pricePerSlice,
        int $sliceInMinutes,
        \DateTimeImmutable $effectiveDate
    ) {
        $this->parkingId = $parkingId;
        $this->startDurationMinute = $startDurationMinute;
        $this->endDurationMinute = $endDurationMinute;
        $this->pricePerSlice = $pricePerSlice;
        $this->sliceInMinutes = $sliceInMinutes;
        $this->effectiveDate = $effectiveDate;
    }
}


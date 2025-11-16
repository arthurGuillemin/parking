<?php

namespace App\Domain\Entity;

class Invoice {
    private int $id;
    private ?int $reservationId;
    private ?int $sessionId;
    private \DateTimeImmutable $issueDate;
    private float $amountHt;
    private float $amountTtc;
    private ?string $detailsJson;
    private string $invoiceType; // e.g., 'reservation', 'subscription', 'session'

    public function __construct(int $id, ?int $reservationId, ?int $sessionId, \DateTimeImmutable $issueDate, float $amountHt, float $amountTtc, ?string $detailsJson, string $invoiceType) {
        $this->id = $id;
        $this->reservationId = $reservationId;
        $this->sessionId = $sessionId;
        $this->issueDate = $issueDate;
        $this->amountHt = $amountHt;
        $this->amountTtc = $amountTtc;
        $this->detailsJson = $detailsJson;
        $this->invoiceType = $invoiceType;
    }

    public function getInvoiceId(): int {
        return $this->id;
    }

    public function getReservationId(): ?int {
        return $this->reservationId;
    }

    public function getSessionId(): ?int {
        return $this->sessionId;
    }

    public function getIssuedDate(): \DateTimeImmutable {
        return $this->issueDate;
    }

    public function getAmountHt(): float {
        return $this->amountHt;
    }

    public function getAmountTtc(): float {
        return $this->amountTtc;
    }

    public function getDetailsJson(): ?array {
        return $this->detailsJson ? json_decode($this->detailsJson, true) : null;
    }

    public function getInvoiceType(): string {
        return $this->invoiceType;
    }
}

<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Invoice;

interface InvoiceRepositoryInterface {
    public function findById(int $id): ?Invoice;

    public function findByReservationId(int $reservationId): ?Invoice;

    public function findBySessionId(int $sessionId): ?Invoice;

    public function save(Invoice $invoice): Invoice;

    /**
     * Retourne toutes les factures d'un parking sur une période donnée.
     * @param int $parkingId
     * @param \DateTimeImmutable $start
     * @param \DateTimeImmutable $end
     * @return Invoice[]
     */
    public function findByParkingIdAndDateRange(int $parkingId, \DateTimeImmutable $start, \DateTimeImmutable $end): array;
}

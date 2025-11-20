<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Invoice;

interface InvoiceRepository
{
    public function findById(int $id): ?Invoice;

    public function findByReservationId(int $reservationId): ?Invoice;

    public function findBySessionId(int $sessionId): ?Invoice;

    public function save(Invoice $invoice): Invoice;
}

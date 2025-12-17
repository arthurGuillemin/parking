<?php

namespace App\Infrastructure\Persistence\File;

use App\Domain\Entity\Invoice;
use App\Domain\Repository\InvoiceRepositoryInterface;
use App\Infrastructure\Storage\FileStorage;
use DateTimeImmutable;
use RuntimeException;

class FileInvoiceRepository implements InvoiceRepositoryInterface
{
    private string $file;
    private FileStorage $storage;


    public function __construct()
    {
        $this->file = __DIR__ . '/storage/invoices.json';

    }

    public function findById(int $id): ?Invoice
    {
        foreach ($this->storage->read() as $row) {
            if ($row['id'] === $id) {
                return $this->mapToInvoice($row);
            }
        }
        return null;
    }

    public function findByReservationId(int $reservationId): ?Invoice
    {
        foreach ($this->storage->read() as $row) {
            if (($row['reservation_id'] ?? null) === $reservationId) {
                return $this->mapToInvoice($row);
            }
        }
        return null;
    }

    public function findBySessionId(int $sessionId): ?Invoice
    {
        foreach ($this->storage->read() as $row) {
            if (($row['session_id'] ?? null) === $sessionId) {
                return $this->mapToInvoice($row);
            }
        }
        return null;
    }

    public function save(Invoice $invoice): Invoice
    {
        $data = $this->storage->read();
        $found = false;

        foreach ($data as &$row) {
            if ($row['id'] === $invoice->getInvoiceId()) {
                $row = $this->mapFromInvoice($invoice);
                $found = true;
                break;
            }
        }

        if (!$found) {
            $data[] = $this->mapFromInvoice($invoice);
        }

        $this->storage->write($data);

        return $invoice;
    }

    public function findByParkingIdAndDateRange(
        int $parkingId,
        DateTimeImmutable $start,
        DateTimeImmutable $end
    ): array {
        $results = [];

        foreach ($this->storage->read() as $row) {
            if (
                ($row['parking_id'] ?? null) === $parkingId &&
                new DateTimeImmutable($row['issue_date']) >= $start &&
                new DateTimeImmutable($row['issue_date']) <= $end
            ) {
                $results[] = $this->mapToInvoice($row);
            }
        }

        return $results;
    }

    public function findByUserId(string $userId): array
    {
        $results = [];

        foreach ($this->storage->read() as $row) {
            if (($row['user_id'] ?? null) === $userId) {
                $results[] = $this->mapToInvoice($row);
            }
        }

        return $results;
    }


    private function mapToInvoice(array $row): Invoice
    {
        return new Invoice(
            id: (int) $row['id'],
            reservationId: $row['reservation_id'] ?? null,
            sessionId: $row['session_id'] ?? null,
            issueDate: new DateTimeImmutable($row['issue_date']),
            amountHt: (float) $row['amount_ht'],
            amountTtc: (float) $row['amount_ttc'],
            detailsJson: $row['details'] ?? null,
            invoiceType: $row['invoice_type']
        );
    }

    private function mapFromInvoice(Invoice $invoice): array
    {
        return [
            'id' => $invoice->getInvoiceId(),
            'reservation_id' => $invoice->getReservationId(),
            'session_id' => $invoice->getSessionId(),
            'issue_date' => $invoice->getIssuedDate()->format('Y-m-d H:i:s'),
            'amount_ht' => $invoice->getAmountHt(),
            'amount_ttc' => $invoice->getAmountTtc(),
            'details' => $invoice->getDetailsJson(),
            'invoice_type' => $invoice->getInvoiceType(),
        ];
    }
}

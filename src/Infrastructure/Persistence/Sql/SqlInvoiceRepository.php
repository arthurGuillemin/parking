<?php

namespace App\Infrastructure\Persistence\Sql;

use App\Domain\Entity\Invoice;
use App\Domain\Repository\InvoiceRepositoryInterface;
use App\Infrastructure\Database\Database;
use PDO;
use PDOException;
use RuntimeException;
use DateTimeImmutable;

class SqlInvoiceRepository implements InvoiceRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    //trouver une facture avec son id
    public function findById(int $id): ?Invoice
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, reservation_id, session_id, issue_date, amount_ht, amount_ttc, details_json, invoice_type
                FROM invoices
                WHERE id = :id

            ");
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch();
            if (!$row)
                return null;
            return $this->mapToInvoice($row);
        } catch (PDOException $e) {
            throw new RuntimeException("aucune facture trouvée avec cet id: " . $e->getMessage());
        }
    }

    //trouver une facture ave l'id de resa

    public function findByReservationId(int $reservationId): ?Invoice
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, reservation_id, session_id, issue_date, amount_ht, amount_ttc, details_json, invoice_type
                FROM invoices
                WHERE reservation_id = :reservation_id

            ");
            $stmt->execute(['reservation_id' => $reservationId]);
            $row = $stmt->fetch();
            if (!$row)
                return null;
            return $this->mapToInvoice($row);
        } catch (PDOException $e) {
            throw new RuntimeException("aucune facture trouvée avec cette id de reservation: " . $e->getMessage());
        }
    }

    // trouver une facture avec l'id de stationnement
    public function findBySessionId(int $sessionId): ?Invoice
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, reservation_id, session_id, issue_date, amount_ht, amount_ttc, details_json, invoice_type
                FROM invoices
                WHERE session_id = :session_id

            ");
            $stmt->execute(['session_id' => $sessionId]);
            $row = $stmt->fetch();
            if (!$row)
                return null;
            return $this->mapToInvoice($row);
        } catch (PDOException $e) {
            throw new RuntimeException("aucune facture trouvé pour cet id de stationnement: " . $e->getMessage());
        }
    }

    public function save(Invoice $invoice): Invoice
    {
        try {
            $existing = $this->findById($invoice->getInvoiceId());
            if ($existing) {
                $stmt = $this->db->prepare("
                    UPDATE invoices
                    SET reservation_id = :reservation_id,
                        session_id = :session_id,
                        issue_date = :issue_date,
                        amount_ht = :amount_ht,
                        amount_ttc = :amount_ttc,
                        details_json = :details_json,
                        invoice_type = :invoice_type
                    WHERE id = :id
                ");
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO invoices (id, reservation_id, session_id, issue_date, amount_ht, amount_ttc, details_json, invoice_type)
                    VALUES (:id, :reservation_id, :session_id, :issue_date, :amount_ht, :amount_ttc, :details_json, :invoice_type)
                ");
            }

            $stmt->execute([
                'id' => $invoice->getInvoiceId(),
                'reservation_id' => $invoice->getReservationId(),
                'session_id' => $invoice->getSessionId(),
                'issue_date' => $invoice->getIssuedDate()->format('Y-m-d H:i:s'),
                'amount_ht' => $invoice->getAmountHt(),
                'amount_ttc' => $invoice->getAmountTtc(),
                'details_json' => $invoice->getDetailsJson() ? json_encode($invoice->getDetailsJson()) : null,

                'invoice_type' => $invoice->getInvoiceType(),
            ]);

            return $invoice;

        } catch (PDOException $e) {
            throw new RuntimeException("erreur dans le save de la facture: " . $e->getMessage());
        }
    }

    public function findByParkingIdAndDateRange(int $parkingId, DateTimeImmutable $start, DateTimeImmutable $end): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT i.id, i.reservation_id, i.session_id, i.issue_date, i.amount_ht, i.amount_ttc, i.details_json, i.invoice_type
                FROM invoices i

                INNER JOIN reservations r ON i.reservation_id = r.id
                INNER JOIN parkings p ON r.parking_id = p.id
                WHERE p.id = :parking_id
                  AND i.issue_date BETWEEN :start AND :end
                  AND r.status = 'completed'
            ");
            $stmt->execute([
                'parking_id' => $parkingId,
                'start' => $start->format('Y-m-d H:i:s'),
                'end' => $end->format('Y-m-d H:i:s'),
            ]);
            $rows = $stmt->fetchAll();
            return array_map([$this, 'mapToInvoice'], $rows);
        } catch (PDOException $e) {
            throw new RuntimeException("Aucune facture trouvée avec cet id et ce daterange: " . $e->getMessage());
        }
    }

    public function findByUserId(string $userId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT i.id, i.reservation_id, i.session_id, i.issue_date, i.amount_ht, i.amount_ttc, i.details_json, i.invoice_type
                FROM invoices i
                LEFT JOIN reservations r ON i.reservation_id = r.id
                LEFT JOIN parking_sessions s ON i.session_id = s.id
                WHERE r.user_id = :user_id OR s.user_id = :user_id
                ORDER BY i.issue_date DESC
            ");
            $stmt->execute(['user_id' => $userId]);
            $rows = $stmt->fetchAll();
            return array_map([$this, 'mapToInvoice'], $rows);
        } catch (PDOException $e) {
            throw new RuntimeException("Erreur de recherche factures utilisateur: " . $e->getMessage());
        }
    }

    private function mapToInvoice(array $row): Invoice
    {
        return new Invoice(
            id: (int) $row['id'],
            reservationId: $row['reservation_id'] !== null ? (int) $row['reservation_id'] : null,
            sessionId: $row['session_id'] !== null ? (int) $row['session_id'] : null,
            issueDate: new DateTimeImmutable($row['issue_date']),
            amountHt: (float) $row['amount_ht'],
            amountTtc: (float) $row['amount_ttc'],
            detailsJson: $row['details_json'],
            invoiceType: $row['invoice_type']

        );
    }
}

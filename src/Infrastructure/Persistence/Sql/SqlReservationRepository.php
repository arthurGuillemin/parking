<?php

namespace App\Infrastructure\Persistence\Sql;

use App\Domain\Entity\Reservation;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Infrastructure\Database\Database;
use PDO;
use PDOException;
use RuntimeException;
use DateTimeImmutable;

class SqlReservationRepository implements ReservationRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }


    //trouver une resa avec son id

    public function findById(int $id): ?Reservation
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, user_id, parking_id, start_datetime, end_datetime, status, calculated_amount, final_amount
                FROM reservations
                WHERE id = :id

            ");
            $stmt->execute(['id' => $id]);

            $row = $stmt->fetch();
            if (!$row)
                return null;

            return $this->mapToReservation($row);

        } catch (PDOException $e) {
            throw new RuntimeException("Failed to fetch reservation by id: " . $e->getMessage());
        }
    }

    //trouver une resa avec un id d'utilisateur

    public function findByUserId(string $userId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, user_id, parking_id, start_datetime, end_datetime, status, calculated_amount, final_amount
                FROM reservations
                WHERE user_id = :user_id
                ORDER BY start_datetime DESC

            ");
            $stmt->execute(['user_id' => $userId]);

            $rows = $stmt->fetchAll();
            return array_map([$this, 'mapToReservation'], $rows);

        } catch (PDOException $e) {
            throw new RuntimeException("Failed to fetch reservations by user: " . $e->getMessage());
        }
    }

    //trouver une resa entre 2 créneaux
    public function findForParkingBetween(int $parkingId, DateTimeImmutable $start, DateTimeImmutable $end): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, user_id, parking_id, start_datetime, end_datetime, status, calculated_amount, final_amount
                FROM reservations
                WHERE parking_id = :parking_id
                  AND start_datetime < :end
                  AND end_datetime > :start
                ORDER BY start_datetime

            ");
            $stmt->execute([
                'parking_id' => $parkingId,
                'start' => $start->format('Y-m-d H:i:s'),
                'end' => $end->format('Y-m-d H:i:s'),
            ]);

            $rows = $stmt->fetchAll();
            return array_map([$this, 'mapToReservation'], $rows);

        } catch (PDOException $e) {
            throw new RuntimeException("Failed to fetch reservations for parking between dates: " . $e->getMessage());
        }
    }
    //trouver toute les resa d'un parking
    public function findAllByParkingId(int $parkingId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, user_id, parking_id, start_datetime, end_datetime, status, calculated_amount, final_amount
                FROM reservations
                WHERE parking_id = :parking_id
                ORDER BY start_datetime DESC

            ");
            $stmt->execute(['parking_id' => $parkingId]);

            $rows = $stmt->fetchAll();
            return array_map([$this, 'mapToReservation'], $rows);

        } catch (PDOException $e) {
            throw new RuntimeException("Failed to fetch all reservations for parking: " . $e->getMessage());
        }
    }

    public function countOverlapping(int $parkingId, \DateTimeImmutable $start, \DateTimeImmutable $end): int
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*)
                FROM reservations
                WHERE parking_id = :parking_id
                  AND start_datetime < :end
                  AND end_datetime > :start
                  AND status IN ('confirmed', 'pending')
            ");
            $stmt->execute([
                'parking_id' => $parkingId,
                'start' => $start->format('Y-m-d H:i:s'),
                'end' => $end->format('Y-m-d H:i:s'),
            ]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to count overlapping reservations: " . $e->getMessage());
        }
    }

    public function countActiveOverstayers(int $parkingId, \DateTimeImmutable $atTime): int
    {
        try {
            // Count sessions that are still active (exit_time IS NULL)
            // BUT their reservation has ended before the check time ($atTime)
            // These people validly occupy a spot but are NOT counted by countOverlapping
            $stmt = $this->db->prepare("
                SELECT COUNT(*)
                FROM parking_sessions s
                JOIN reservations r ON s.reservation_id = r.id
                WHERE s.parking_id = :parking_id
                  AND s.exit_time IS NULL
                  AND r.end_datetime < :at_time
            ");
            $stmt->execute([
                'parking_id' => $parkingId,
                'at_time' => $atTime->format('Y-m-d H:i:s'),
            ]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to count active overstayers: " . $e->getMessage());
        }
    }

    public function findActiveReservation(string $userId, int $parkingId, \DateTimeImmutable $atTime): ?Reservation
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, user_id, parking_id, start_datetime, end_datetime, status, calculated_amount, final_amount
                FROM reservations
                WHERE user_id = :user_id
                  AND parking_id = :parking_id
                  AND start_datetime <= :at_time
                  AND end_datetime >= :at_time
                  AND status IN ('confirmed', 'pending')
                LIMIT 1
            ");
            $stmt->execute([
                'user_id' => $userId,
                'parking_id' => $parkingId,
                'at_time' => $atTime->format('Y-m-d H:i:s'),
            ]);
            $row = $stmt->fetch();
            if (!$row) {
                return null;
            }
            return $this->mapToReservation($row);
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to find active reservation: " . $e->getMessage());
        }
    }

    //save une resa

    public function save(Reservation $reservation): Reservation
    {
        try {
            $existing = $this->findById($reservation->getReservationId());

            if ($existing) {
                $stmt = $this->db->prepare("
                    UPDATE reservations
                    SET user_id = :user_id,
                        parking_id = :parking_id,
                        start_datetime = :start_datetime,
                        end_datetime = :end_datetime,
                        status = :status,
                        calculated_amount = :calculated_amount,
                        final_amount = :final_amount
                    WHERE id = :id
                ");

                $stmt->execute([
                    'id' => $reservation->getReservationId(),
                    'user_id' => $reservation->getUserId(),
                    'parking_id' => $reservation->getParkingId(),
                    'start_datetime' => $reservation->getStartDateTime()->format('Y-m-d H:i:s'),
                    'end_datetime' => $reservation->getEndDateTime()->format('Y-m-d H:i:s'),
                    'status' => $reservation->getStatus(),
                    'calculated_amount' => $reservation->getCalculatedAmount(),
                    'final_amount' => $reservation->getFinalAmount(),
                ]);
                return $reservation;
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO reservations (user_id, parking_id, start_datetime, end_datetime, status, calculated_amount, final_amount)
                    VALUES (:user_id, :parking_id, :start_datetime, :end_datetime, :status, :calculated_amount, :final_amount)
                ");
                $stmt->execute([
                    'user_id' => $reservation->getUserId(),
                    'parking_id' => $reservation->getParkingId(),
                    'start_datetime' => $reservation->getStartDateTime()->format('Y-m-d H:i:s'),
                    'end_datetime' => $reservation->getEndDateTime()->format('Y-m-d H:i:s'),
                    'status' => $reservation->getStatus(),
                    'calculated_amount' => $reservation->getCalculatedAmount(),
                    'final_amount' => $reservation->getFinalAmount(),
                ]);

                $newId = (int) $this->db->lastInsertId();

                // Return new entity with generated ID
                return new Reservation(
                    $newId,
                    $reservation->getUserId(),
                    $reservation->getParkingId(),
                    $reservation->getStartDateTime(),
                    $reservation->getEndDateTime(),
                    $reservation->getStatus(),
                    $reservation->getCalculatedAmount(),
                    $reservation->getFinalAmount()
                );
            }

        } catch (PDOException $e) {
            throw new RuntimeException("Failed to save reservation: " . $e->getMessage());
        }
    }

    private function mapToReservation(array $row): Reservation
    {
        return new Reservation(
            id: (int) $row['id'],
            userId: $row['user_id'],
            parkingId: (int) $row['parking_id'],
            startDateTime: new DateTimeImmutable($row['start_datetime']),
            endDateTime: new DateTimeImmutable($row['end_datetime']),

            status: $row['status'],
            calculatedAmount: $row['calculated_amount'] !== null ? (float) $row['calculated_amount'] : null,
            finalAmount: $row['final_amount'] !== null ? (float) $row['final_amount'] : null
        );
    }
}

<?php

namespace App\Infrastructure\Persistence\Sql;

use App\Domain\Entity\ParkingSession;
use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Infrastructure\Database\Database;
use PDO;
use PDOException;
use RuntimeException;
use DateTimeImmutable;

class SqlParkingSessionRepository implements ParkingSessionRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    //trouver un stationnement avec son id

    public function findById(int $id): ?ParkingSession
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, user_id, parking_id, reservation_id, entry_time, exit_time, final_amount, penalty_applied
                FROM parking_sessions
                WHERE id = :id



            ");
            $stmt->execute(['id' => $id]);

            $row = $stmt->fetch();
            if (!$row)
                return null;

            return $this->mapToParkingSession($row);

        } catch (PDOException $e) {
            throw new RuntimeException("Aucun stationnement trouvé avec cet id : " . $e->getMessage());
        }
    }

    //trouver un stationnement avec l'id utilisateur
    //trouver un stationnement avec l'id utilisateur

    public function findActiveSessionByUserId(string $userId): ?ParkingSession
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, user_id, parking_id, reservation_id, entry_time, exit_time, final_amount, penalty_applied
                FROM parking_sessions
                WHERE user_id = :user_id AND exit_time IS NULL
                LIMIT 1
            ");
            $stmt->execute(['user_id' => $userId]);

            $row = $stmt->fetch();
            if (!$row)
                return null;

            return $this->mapToParkingSession($row);

        } catch (PDOException $e) {
            throw new RuntimeException("Aucun stationnement trouvé avec cet id d'utilisateur: " . $e->getMessage());
        }
    }

    public function findByUserId(string $userId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, user_id, parking_id, reservation_id, entry_time, exit_time, final_amount, penalty_applied
                FROM parking_sessions
                WHERE user_id = :user_id
                ORDER BY entry_time DESC
            ");
            $stmt->execute(['user_id' => $userId]);

            $rows = $stmt->fetchAll();
            return array_map([$this, 'mapToParkingSession'], $rows);

        } catch (PDOException $e) {
            throw new RuntimeException("Erreur de récupération de l'historique : " . $e->getMessage());
        }
    }
    //trouver un stationnement avec un id de resa
    public function findByReservationId(int $reservationId): ?ParkingSession
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, user_id, parking_id, reservation_id, entry_time, exit_time, final_amount, penalty_applied
                FROM parking_sessions
                WHERE reservation_id = :reservation_id



            ");
            $stmt->execute(['reservation_id' => $reservationId]);

            $row = $stmt->fetch();
            if (!$row)
                return null;

            return $this->mapToParkingSession($row);

        } catch (PDOException $e) {
            throw new RuntimeException("Aucun stationnement trouvé avec cet id de resa: " . $e->getMessage());
        }
    }

    //trouver toutes les sessions de stationnement d'un parking avec son id

    public function findByParkingId(int $parkingId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, user_id, parking_id, reservation_id, entry_time, exit_time, final_amount, penalty_applied
                FROM parking_sessions
                WHERE parking_id = :parking_id
                ORDER BY entry_time DESC



            ");
            $stmt->execute(['parking_id' => $parkingId]);
            $rows = $stmt->fetchAll();
            return array_map([$this, 'mapToParkingSession'], $rows);
        } catch (PDOException $e) {
            throw new RuntimeException("Aucun stationnement trouvé avec cet id de parking: " . $e->getMessage());
        }
    }
    //save un stationnement

    public function save(ParkingSession $session): ParkingSession
    {
        try {
            $existing = null;
            if ($session->getSessionId() !== 0) {
                $existing = $this->findById($session->getSessionId());
            }

            if ($existing) {
                $stmt = $this->db->prepare("
                    UPDATE parking_sessions
                    SET user_id = :user_id,
                        parking_id = :parking_id,
                        reservation_id = :reservation_id,
                        entry_time = :entry_time,
                        exit_time = :exit_time,
                        final_amount = :final_amount,
                        penalty_applied = :penalty_applied
                    WHERE id = :id
                ");
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO parking_sessions (user_id, parking_id, reservation_id, entry_time, exit_time, final_amount, penalty_applied)
                    VALUES (:user_id, :parking_id, :reservation_id, :entry_time, :exit_time, :final_amount, :penalty_applied)
                ");
            }



            $params = [
                'user_id' => $session->getUserId(),
                'parking_id' => $session->getParkingId(),
                'reservation_id' => $session->getReservationId(),
                'entry_time' => $session->getEntryDateTime()->format('Y-m-d H:i:s'),
                'exit_time' => $session->getExitDateTime()?->format('Y-m-d H:i:s'),
                'final_amount' => $session->getFinalAmount(),
                'penalty_applied' => $session->isPenaltyApplied() ? 1 : 0,
            ];

            if ($existing) {
                $params['id'] = $session->getSessionId();
            }

            $stmt->execute($params);

            if (!$existing) {
                $newId = (int) $this->db->lastInsertId();
                return new ParkingSession(
                    $newId,
                    $session->getUserId(),
                    $session->getParkingId(),
                    $session->getReservationId(),
                    $session->getEntryDateTime(),
                    $session->getExitDateTime(),
                    $session->getFinalAmount(),
                    $session->isPenaltyApplied()
                );
            }

            return $session;

        } catch (PDOException $e) {
            throw new RuntimeException("Failed to save parking session: " . $e->getMessage());
        }
    }

    private function mapToParkingSession(array $row): ParkingSession
    {
        return new ParkingSession(
            id: (int) $row['id'],
            userId: $row['user_id'],
            parkingId: (int) $row['parking_id'],
            reservationId: $row['reservation_id'] !== null ? (int) $row['reservation_id'] : null,
            entryDateTime: new DateTimeImmutable($row['entry_time']),
            exitDateTime: $row['exit_time'] !== null ? new DateTimeImmutable($row['exit_time']) : null,



            finalAmount: $row['final_amount'] !== null ? (float) $row['final_amount'] : null,
            penaltyApplied: (bool) $row['penalty_applied']
        );
    }
}

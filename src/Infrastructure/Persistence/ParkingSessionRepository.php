<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\ParkingSession;
use App\Domain\Repository\ParkingSessionRepositoryInterface;
use PDO;

class ParkingSessionRepository implements ParkingSessionRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?ParkingSession
    {
        $stmt = $this->pdo->prepare('SELECT * FROM parking_sessions WHERE id = ?');
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->mapToEntity($data) : null;
    }

    public function findActiveSessionByUserId(string $userId): ?ParkingSession
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM parking_sessions 
             WHERE user_id = ? 
             AND exit_date IS NULL'
        );
        $stmt->execute([$userId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->mapToEntity($data) : null;
    }

    public function findByReservationId(int $reservationId): ?ParkingSession
    {
        $stmt = $this->pdo->prepare('SELECT * FROM parking_sessions WHERE reservation_id = ?');
        $stmt->execute([$reservationId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->mapToEntity($data) : null;
    }

    public function findByParkingId(int $parkingId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM parking_sessions WHERE parking_id = ?');
        $stmt->execute([$parkingId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => $this->mapToEntity($row), $rows);
    }

    public function save(ParkingSession $session): ParkingSession
    {
        if ($session->getSessionId() === 0) {
            return $this->insert($session);
        } else {
            return $this->update($session);
        }
    }

    private function insert(ParkingSession $session): ParkingSession
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO parking_sessions 
             (user_id, parking_id, reservation_id, entry_date, exit_date, final_amount, penalty_applied) 
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $session->getUserId(),
            $session->getParkingId(),
            $session->getReservationId(),
            $session->getEntryDateTime()->format('Y-m-d H:i:s'),
            $session->getExitDateTime()?->format('Y-m-d H:i:s'),
            $session->getFinalAmount(),
            $session->isPenaltyApplied() ? 1 : 0
        ]);

        $id = (int) $this->pdo->lastInsertId();

        return new ParkingSession(
            $id,
            $session->getUserId(),
            $session->getParkingId(),
            $session->getReservationId(),
            $session->getEntryDateTime(),
            $session->getExitDateTime(),
            $session->getFinalAmount(),
            $session->isPenaltyApplied()
        );
    }

    private function update(ParkingSession $session): ParkingSession
    {
        $stmt = $this->pdo->prepare(
            'UPDATE parking_sessions 
             SET exit_date = ?, final_amount = ?, penalty_applied = ? 
             WHERE id = ?'
        );

        $stmt->execute([
            $session->getExitDateTime()?->format('Y-m-d H:i:s'),
            $session->getFinalAmount(),
            $session->isPenaltyApplied() ? 1 : 0,
            $session->getSessionId()
        ]);

        return $session;
    }

    private function mapToEntity(array $data): ParkingSession
    {
        return new ParkingSession(
            (int) $data['id'],
            $data['user_id'],
            (int) $data['parking_id'],
            $data['reservation_id'] ? (int) $data['reservation_id'] : null,
            new \DateTimeImmutable($data['entry_date']),
            $data['exit_date'] ? new \DateTimeImmutable($data['exit_date']) : null,
            $data['final_amount'] !== null ? (float) $data['final_amount'] : null,
            (bool) $data['penalty_applied']
        );
    }
}

<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Reservation;
use App\Domain\Repository\ReservationRepositoryInterface;
use PDO;

class ReservationRepository implements ReservationRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?Reservation
    {
        $stmt = $this->pdo->prepare('SELECT * FROM reservations WHERE id = ?');
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->mapToEntity($data) : null;
    }

    public function findByUserId(string $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM reservations WHERE user_id = ?');
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => $this->mapToEntity($row), $rows);
    }

    public function findForParkingBetween(int $parkingId, \DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM reservations 
             WHERE parking_id = ? 
             AND start_date < ? 
             AND end_date > ?'
        );
        $stmt->execute([$parkingId, $end->format('Y-m-d H:i:s'), $start->format('Y-m-d H:i:s')]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => $this->mapToEntity($row), $rows);
    }

    public function findAllByParkingId(int $parkingId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM reservations WHERE parking_id = ?');
        $stmt->execute([$parkingId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => $this->mapToEntity($row), $rows);
    }

    public function countOverlapping(int $parkingId, \DateTimeImmutable $start, \DateTimeImmutable $end): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM reservations 
             WHERE parking_id = ? 
             AND start_date < ? 
             AND end_date > ? 
             AND status != "cancelled"'
        );
        $stmt->execute([
            $parkingId,
            $end->format('Y-m-d H:i:s'),
            $start->format('Y-m-d H:i:s')
        ]);
        return (int) $stmt->fetchColumn();
    }

    public function findActiveReservation(string $userId, int $parkingId, \DateTimeImmutable $atTime): ?Reservation
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM reservations 
             WHERE user_id = ? 
             AND parking_id = ? 
             AND start_date <= ? 
             AND end_date >= ?
             AND status = "active" OR status = "pending"'
        );
        $stmt = $this->pdo->prepare(
            "SELECT * FROM reservations 
             WHERE user_id = ? 
             AND parking_id = ? 
             AND start_date <= ? 
             AND end_date >= ? 
             AND status IN ('pending', 'active')"
        );

        $stmt->execute([
            $userId,
            $parkingId,
            $atTime->format('Y-m-d H:i:s'),
            $atTime->format('Y-m-d H:i:s')
        ]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->mapToEntity($data) : null;
    }


    public function save(Reservation $reservation): Reservation
    {
        if ($reservation->getReservationId() === 0) {
            return $this->insert($reservation);
        } else {
            return $this->update($reservation);
        }
    }

    private function insert(Reservation $reservation): Reservation
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO reservations 
             (user_id, parking_id, start_date, end_date, status, calculated_amount, final_amount) 
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $reservation->getUserId(),
            $reservation->getParkingId(),
            $reservation->getStartDateTime()->format('Y-m-d H:i:s'),
            $reservation->getEndDateTime()->format('Y-m-d H:i:s'),
            $reservation->getStatus(),
            $reservation->getCalculatedAmount(),
            $reservation->getFinalAmount()
        ]);

        $id = (int) $this->pdo->lastInsertId();

        // Return new object with ID
        return new Reservation(
            $id,
            $reservation->getUserId(),
            $reservation->getParkingId(),
            $reservation->getStartDateTime(),
            $reservation->getEndDateTime(),
            $reservation->getStatus(),
            $reservation->getCalculatedAmount(),
            $reservation->getFinalAmount()
        );
    }

    private function update(Reservation $reservation): Reservation
    {
        $stmt = $this->pdo->prepare(
            'UPDATE reservations 
             SET status = ?, end_date = ?, final_amount = ? 
             WHERE id = ?'
        );
        // Only updating fields that change typically for efficiency, or all?
        // Updating all relevant:
        $stmt->execute([
            $reservation->getStatus(),
            $reservation->getEndDateTime()->format('Y-m-d H:i:s'),
            $reservation->getFinalAmount(),
            $reservation->getReservationId()
        ]);

        return $reservation;
    }

    private function mapToEntity(array $data): Reservation
    {
        return new Reservation(
            (int) $data['id'],
            $data['user_id'],
            (int) $data['parking_id'],
            new \DateTimeImmutable($data['start_date']),
            new \DateTimeImmutable($data['end_date']),
            $data['status'],
            $data['calculated_amount'] !== null ? (float) $data['calculated_amount'] : null,
            $data['final_amount'] !== null ? (float) $data['final_amount'] : null
        );
    }
}

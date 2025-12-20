<?php

namespace App\Infrastructure\Persistence\File;

use App\Domain\Entity\ParkingSession;
use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Infrastructure\Storage\FileStorage;
use DateTimeImmutable;

/**
 * Implémentation FILE du ParkingSessionRepository
 * Stockage des sessions de stationnement dans un fichier JSON
 */
class FileParkingSessionRepository implements ParkingSessionRepositoryInterface
{
    private FileStorage $storage;

    public function __construct()
    {
        $this->storage = new FileStorage(__DIR__ . '/storage/parking-sessions.json');
    }

    /**
     * Trouver une session par son id
     */
    public function findById(int $id): ?ParkingSession
    {
        foreach ($this->storage->read() as $row) {
            if ($row['id'] === $id) {
                return $this->mapToParkingSession($row);
            }
        }

        return null;
    }

    /**
     * Trouver la session active d’un utilisateur
     */
    public function findActiveSessionByUserId(string $userId): ?ParkingSession
    {
        foreach ($this->storage->read() as $row) {
            if (
                $row['user_id'] === $userId &&
                $row['exit_time'] === null
            ) {
                return $this->mapToParkingSession($row);
            }
        }

        return null;
    }

    /**
     * Historique des sessions d’un utilisateur
     */
    public function findByUserId(string $userId): array
    {
        $results = [];

        foreach ($this->storage->read() as $row) {
            if ($row['user_id'] === $userId) {
                $results[] = $this->mapToParkingSession($row);
            }
        }

        return $results;
    }

    /**
     * Trouver une session par réservation
     */
    public function findByReservationId(int $reservationId): ?ParkingSession
    {
        foreach ($this->storage->read() as $row) {
            if (($row['reservation_id'] ?? null) === $reservationId) {
                return $this->mapToParkingSession($row);
            }
        }

        return null;
    }

    /**
     * Sessions d’un parking
     */
    public function findByParkingId(int $parkingId): array
    {
        $results = [];

        foreach ($this->storage->read() as $row) {
            if ($row['parking_id'] === $parkingId) {
                $results[] = $this->mapToParkingSession($row);
            }
        }

        return $results;
    }

    /**
     * Sauvegarde une session
     */
    public function save(ParkingSession $session): ParkingSession
    {
        $data = $this->storage->read();
        $found = false;

        foreach ($data as &$row) {
            if ($row['id'] === $session->getSessionId()) {
                $row = $this->mapFromParkingSession($session);
                $found = true;
                break;
            }
        }

        if (!$found) {
            $data[] = $this->mapFromParkingSession($session);
        }

        $this->storage->write($data);

        return $session;
    }

    /* =========================
       MAPPERS
    ========================= */

    private function mapToParkingSession(array $row): ParkingSession
    {
        return new ParkingSession(
            id: (int) $row['id'],
            userId: $row['user_id'],
            parkingId: (int) $row['parking_id'],
            reservationId: $row['reservation_id'] ?? null,
            entryDateTime: new DateTimeImmutable($row['entry_time']),
            exitDateTime: $row['exit_time'] !== null
                ? new DateTimeImmutable($row['exit_time'])
                : null,
            finalAmount: $row['final_amount'] ?? null,
            penaltyApplied: (bool) $row['penalty_applied']
        );
    }

    private function mapFromParkingSession(ParkingSession $session): array
    {
        return [
            'id' => $session->getSessionId(),
            'user_id' => $session->getUserId(),
            'parking_id' => $session->getParkingId(),
            'reservation_id' => $session->getReservationId(),
            'entry_time' => $session->getEntryDateTime()->format('Y-m-d H:i:s'),
            'exit_time' => $session->getExitDateTime()?->format('Y-m-d H:i:s'),
            'final_amount' => $session->getFinalAmount(),
            'penalty_applied' => $session->isPenaltyApplied(),
        ];
    }
}

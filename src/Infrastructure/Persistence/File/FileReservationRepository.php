<?php

namespace App\Infrastructure\Persistence\File;

use App\Domain\Entity\Reservation;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Infrastructure\Storage\FileStorage;
use DateTimeImmutable;

class FileReservationRepository implements ReservationRepositoryInterface
{
    private FileStorage $storage;

    public function __construct()
    {
        $this->storage = new FileStorage(__DIR__ . '/storage/reservations.json');
    }

    public function findById(int $id): ?Reservation
    {
        foreach ($this->storage->read() as $row) {
            if ($row['id'] === $id) {
                return $this->mapToReservation($row);
            }
        }
        return null;
    }

    public function findByUserId(string $userId): array
    {
        $results = [];

        foreach ($this->storage->read() as $row) {
            if ($row['user_id'] === $userId) {
                $results[] = $this->mapToReservation($row);
            }
        }

        return $results;
    }

    public function findForParkingBetween(int $parkingId, DateTimeImmutable $start, DateTimeImmutable $end): array
    {
        $results = [];

        foreach ($this->storage->read() as $row) {
            if (
                $row['parking_id'] === $parkingId &&
                new DateTimeImmutable($row['start_datetime']) < $end &&
                new DateTimeImmutable($row['end_datetime']) > $start
            ) {
                $results[] = $this->mapToReservation($row);
            }
        }

        return $results;
    }

    public function findAllByParkingId(int $parkingId): array
    {
        $results = [];

        foreach ($this->storage->read() as $row) {
            if ($row['parking_id'] === $parkingId) {
                $results[] = $this->mapToReservation($row);
            }
        }

        return $results;
    }

    public function save(Reservation $reservation): Reservation
    {
        $data = $this->storage->read();
        $found = false;

        foreach ($data as &$row) {
            if ($row['id'] === $reservation->getReservationId()) {
                $row = $this->mapFromReservation($reservation);
                $found = true;
                break;
            }
        }

        if (!$found) {
            $data[] = $this->mapFromReservation($reservation);
        }

        $this->storage->write($data);

        return $reservation;
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
            calculatedAmount: $row['calculated_amount'],
            finalAmount: $row['final_amount']
        );
    }

    private function mapFromReservation(Reservation $reservation): array
    {
        return [
            'id' => $reservation->getReservationId(),
            'user_id' => $reservation->getUserId(),
            'parking_id' => $reservation->getParkingId(),
            'start_datetime' => $reservation->getStartDateTime()->format('Y-m-d H:i:s'),
            'end_datetime' => $reservation->getEndDateTime()->format('Y-m-d H:i:s'),
            'status' => $reservation->getStatus(),
            'calculated_amount' => $reservation->getCalculatedAmount(),
            'final_amount' => $reservation->getFinalAmount(),
        ];
    }
}

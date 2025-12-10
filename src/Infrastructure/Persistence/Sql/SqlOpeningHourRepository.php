<?php

namespace App\Infrastructure\Persistence\Sql;

use App\Domain\Entity\OpeningHour;
use App\Domain\Repository\OpeningHourRepositoryInterface;
use App\Infrastructure\Database\Database;
use PDO;
use PDOException;
use RuntimeException;
use DateTimeImmutable;

class SqlOpeningHourRepository implements OpeningHourRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    //trouver une plage horaire avec son id
    public function findById(int $id): ?OpeningHour
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, parking_id, weekdayStart , weekdayStart, opening_time, closing_time
                FROM opening_hours
                WHERE id = :id
            ");
            $stmt->execute(['id' => $id]);

            $row = $stmt->fetch();
            if (!$row) return null;

            return $this->mapToOpeningHour($row);

        } catch (PDOException $e) {
            throw new RuntimeException("aucune plage horaire trouvée avec cet id: " . $e->getMessage());
        }
    }

        //trouver une plage horaire avec l'id du parking

    public function findByParkingId(int $parkingId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, parking_id, weekdayStart, weekdayEnd, opening_time, closing_time
                FROM opening_hours
                WHERE parking_id = :parking_id
                ORDER BY weekdayStart
            ");
            $stmt->execute(['parking_id' => $parkingId]);

            $rows = $stmt->fetchAll();
            return array_map([$this, 'mapToOpeningHour'], $rows);

        } catch (PDOException $e) {
            throw new RuntimeException("aucune plage horaire trouvée avec cet id de parking: " . $e->getMessage());
        }
    }

        //save une nouvelle une plage horaire

    public function save(OpeningHour $hour): OpeningHour
    {
        try {
            $existing = $this->findById($hour->getOpeningHourId());
            if ($existing) {
                $stmt = $this->db->prepare("
                    UPDATE opening_hours
                    SET parking_id = :parking_id,
                        weekdayStart = :weekdayStart,
                        weekdayEnd = :weekdayEnd,
                        opening_time = :opening_time,
                        closing_time = :closing_time
                    WHERE id = :id
                ");
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO opening_hours (id, parking_id, weekdayStart, weekdayEnd opening_time, closing_time)
                    VALUES (:id, :parking_id, :weekdayStart, :weekdayEnd, :opening_time, :closing_time)
                ");
            }
            $stmt->execute([
                'id'           => $hour->getOpeningHourId(),
                'parking_id'   => $hour->getParkingId(),
                'weekdayStart'      => $hour->getWeekdayStart(),
                'weekdayEnd' => $hour->getWeekdayEnd(),
                'opening_time' => $hour->getOpeningTime()->format('H:i:s'),
                'closing_time' => $hour->getClosingTime()->format('H:i:s'),
            ]);
            return $hour;
        } catch (PDOException $e) {
            throw new RuntimeException("erreur dans le save de la plage horaire: " . $e->getMessage());
        }
    }

    private function mapToOpeningHour(array $row): OpeningHour
    {
        return new OpeningHour(
            id: (int)$row['id'],
            parkingId: (int)$row['parking_id'],
            weekdayStart: (int)$row['weekdayStart'],
            weekdayEnd: (int)$row['weekdayEnd'],
            openingTime: new DateTimeImmutable($row['opening_time']),
            closingTime: new DateTimeImmutable($row['closing_time'])
        );
    }
}

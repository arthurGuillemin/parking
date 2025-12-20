<?php

namespace App\Infrastructure\Persistence\File;

use App\Domain\Entity\OpeningHour;
use App\Domain\Repository\OpeningHourRepositoryInterface;
use App\Infrastructure\Storage\FileStorage;
use DateTimeImmutable;


class FileOpeningHourRepository implements OpeningHourRepositoryInterface
{
    private FileStorage $storage;

    public function __construct()
    {
        $this->storage = new FileStorage(__DIR__ . '/storage/opening-hours.json');
    }

    public function findById(int $id): ?OpeningHour
    {
        foreach ($this->storage->read() as $row) {
            if ($row['id'] === $id) {
                return $this->mapToOpeningHour($row);
            }
        }
        return null;
    }

    public function findByParkingId(int $parkingId): array
    {
        $results = [];
        foreach ($this->storage->read() as $row) {
            if (($row['parking_id'] ?? null) === $parkingId) {
                $results[] = $this->mapToOpeningHour($row);
            }
        }
        // tri par jourss de la semaine (weekdayStart)
        usort(
            $results,
            fn (OpeningHour $a, OpeningHour $b) =>
                $a->getWeekdayStart() <=> $b->getWeekdayStart()
        );
        return $results;
    }

    public function save(OpeningHour $hour): OpeningHour
    {
        $data = $this->storage->read();
        $found = false;

        foreach ($data as &$row) {
            if ($row['id'] === $hour->getOpeningHourId()) {
                $row = $this->mapFromOpeningHour($hour);
                $found = true;
                break;
            }
        }
        if (!$found) {
            $data[] = $this->mapFromOpeningHour($hour);
        }
        $this->storage->write($data);

        return $hour;
    }

    public function delete(int $id): void
    {
        $data = array_filter(
            $this->storage->read(),
            fn ($row) => $row['id'] !== $id
        );
        $this->storage->write(array_values($data));
    }

    private function mapToOpeningHour(array $row): OpeningHour
    {
        return new OpeningHour(
            id: (int) $row['id'],
            parkingId: (int) $row['parking_id'],
            weekdayStart: (int) $row['weekdayStart'],
            weekdayEnd: (int) $row['weekdayEnd'],
            openingTime: new DateTimeImmutable($row['opening_time']),
            closingTime: new DateTimeImmutable($row['closing_time'])
        );
    }

    private function mapFromOpeningHour(OpeningHour $hour): array
    {
        return [
            'id' => $hour->getOpeningHourId(),
            'parking_id' => $hour->getParkingId(),
            'weekdayStart' => $hour->getWeekdayStart(),
            'weekdayEnd' => $hour->getWeekdayEnd(),
            'opening_time' => $hour->getOpeningTime()->format('H:i:s'),
            'closing_time' => $hour->getClosingTime()->format('H:i:s'),
        ];
    }
}

<?php

namespace App\Application\UseCase\Owner\AddOpeningHour;

use App\Domain\Repository\OpeningHourRepositoryInterface;
use App\Domain\Entity\OpeningHour;

class AddOpeningHourUseCase
{
    private OpeningHourRepositoryInterface $openingHourRepository;

    public function __construct(OpeningHourRepositoryInterface $openingHourRepository)
    {
        $this->openingHourRepository = $openingHourRepository;
    }

    private function validateWeekdays(int $weekdayStart, int $weekdayEnd): array
    {
        if ($weekdayStart < 1 || $weekdayStart > 7) {
            throw new \InvalidArgumentException("Le premier jour d'ouverture (weekdayStart) doit être entre 1 (lundi) et 7 (dimanche).");
        }
        if ($weekdayEnd < 1 || $weekdayEnd > 7) {
            throw new \InvalidArgumentException("Le dernier jour d'ouverture (weekdayEnd) doit être entre 1 (lundi) et 7 (dimanche).");
        }

        if ($weekdayStart > $weekdayEnd) {
            throw new \InvalidArgumentException("Pour une plage horaire simple, le jour de début doit être avant ou égal au jour de fin.");
        }
        return [$weekdayStart, $weekdayEnd];
    }

    public function execute(int $parkingId, int $weekdayStart, int $weekdayEnd, string $openingTime, string $closingTime): OpeningHour
    {
        [$weekdayStart, $weekdayEnd] = $this->validateWeekdays($weekdayStart, $weekdayEnd);

        $newStart = new \DateTimeImmutable($openingTime);
        $newEnd = new \DateTimeImmutable($closingTime);

        $existingHours = $this->openingHourRepository->findByParkingId($parkingId);

        foreach ($existingHours as $hour) {
            if ($this->weekdaysOverlap($weekdayStart, $weekdayEnd, $hour->getWeekdayStart(), $hour->getWeekdayEnd())) {

                if ($this->timesOverlap($newStart, $newEnd, $hour->getOpeningTime(), $hour->getClosingTime())) {
                    throw new \RuntimeException("Cette plage horaire chevauche une plage existante.");
                }
            }
        }

        $openingHour = new OpeningHour(
            0, // New ID
            $parkingId,
            $weekdayStart,
            $weekdayEnd,
            $newStart,
            $newEnd
        );

        return $this->openingHourRepository->save($openingHour);
    }

    private function weekdaysOverlap(int $s1, int $e1, int $s2, int $e2): bool
    {
        return max($s1, $s2) <= min($e1, $e2);
    }

    private function timesOverlap(\DateTimeImmutable $start1, \DateTimeImmutable $end1, \DateTimeImmutable $start2, \DateTimeImmutable $end2): bool
    {
        $t1Start = (int) $start1->format('Hi');
        $t1End = (int) $end1->format('Hi');
        $t2Start = (int) $start2->format('Hi');
        $t2End = (int) $end2->format('Hi');

        return max($t1Start, $t2Start) < min($t1End, $t2End);
    }
}

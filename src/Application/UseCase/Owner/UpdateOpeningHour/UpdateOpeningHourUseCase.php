<?php

namespace App\Application\UseCase\Owner\UpdateOpeningHour;

use App\Domain\Repository\OpeningHourRepositoryInterface;
use App\Domain\Entity\OpeningHour;

class UpdateOpeningHourUseCase
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
            throw new \InvalidArgumentException("Le premier jour d'ouverture (weekdayStart) ne peut pas être après le dernier jour (weekdayEnd).");
        }
        return [$weekdayStart, $weekdayEnd];
    }

    public function execute(UpdateOpeningHourRequest $request): OpeningHour
    {
        [$weekdayStart, $weekdayEnd] = $this->validateWeekdays($request->weekdayStart, $request->weekdayEnd);
        $openingHour = new OpeningHour(
            0, // id auto-incrémenté par la DB
            $request->parkingId,
            $weekdayStart,
            $weekdayEnd,
            new \DateTimeImmutable($request->openingTime),
            new \DateTimeImmutable($request->closingTime)
        );
        return $this->openingHourRepository->save($openingHour);
    }
}

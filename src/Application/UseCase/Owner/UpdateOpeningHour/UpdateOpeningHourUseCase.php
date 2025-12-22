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

        // Vérifier si des heures d'ouverture existent pour ce parking
        $existingHours = $this->openingHourRepository->findByParkingId($request->parkingId);

        $openingHour = null;

        if (count($existingHours) > 0) {
            // Mettre à jour le premier enregistrement existant
            $first = $existingHours[0];
            $openingHour = new OpeningHour(
                $first->getOpeningHourId(),
                $request->parkingId,
                $weekdayStart,
                $weekdayEnd,
                new \DateTimeImmutable($request->openingTime),
                new \DateTimeImmutable($request->closingTime)
            );

            // Supprimer les enregistrements dupliqués si nécessaire
            for ($i = 1; $i < count($existingHours); $i++) {
                $this->openingHourRepository->delete($existingHours[$i]->getOpeningHourId());
            }
        } else {
            // Créer un nouveau enregistrement
            $openingHour = new OpeningHour(
                0,
                $request->parkingId,
                $weekdayStart,
                $weekdayEnd,
                new \DateTimeImmutable($request->openingTime),
                new \DateTimeImmutable($request->closingTime)
            );
        }

        return $this->openingHourRepository->save($openingHour);
    }
}

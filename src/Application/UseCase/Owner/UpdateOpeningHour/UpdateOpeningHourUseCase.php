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

    public function execute(UpdateOpeningHourRequest $request): OpeningHour
    {
        $openingHour = new OpeningHour(
            0, // id auto-incrémenté par la DB
            $request->parkingId,
            $request->weekdayStart,
            $request->weekdayEnd,
            new \DateTimeImmutable($request->openingTime),
            new \DateTimeImmutable($request->closingTime)
        );
        return $this->openingHourRepository->save($openingHour);
    }
}

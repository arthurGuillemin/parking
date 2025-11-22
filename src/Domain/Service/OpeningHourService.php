<?php

namespace App\Domain\Service;

use App\Domain\Repository\OpeningHourRepositoryInterface;
use App\Application\UseCase\Owner\UpdateOpeningHour\UpdateOpeningHourUseCase;
use App\Application\UseCase\Owner\UpdateOpeningHour\UpdateOpeningHourRequest;
use App\Domain\Entity\OpeningHour;

class OpeningHourService
{
    private OpeningHourRepositoryInterface $openingHourRepository;
    private UpdateOpeningHourUseCase $updateOpeningHourUseCase;

    public function __construct(OpeningHourRepositoryInterface $openingHourRepository)
    {
        $this->openingHourRepository = $openingHourRepository;
        $this->updateOpeningHourUseCase = new UpdateOpeningHourUseCase($openingHourRepository);
    }

    public function updateOpeningHour(UpdateOpeningHourRequest $request): OpeningHour
    {
        return $this->updateOpeningHourUseCase->execute($request);
    }
}


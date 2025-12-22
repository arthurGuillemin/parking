<?php

namespace App\Domain\Service;

use App\Domain\Repository\OpeningHourRepositoryInterface;
use App\Application\UseCase\Owner\AddOpeningHour\AddOpeningHourUseCase;
use App\Application\UseCase\Owner\DeleteOpeningHour\DeleteOpeningHourUseCase;
use App\Domain\Entity\OpeningHour;

class OpeningHourService
{
    private OpeningHourRepositoryInterface $openingHourRepository;
    private AddOpeningHourUseCase $addOpeningHourUseCase;
    private DeleteOpeningHourUseCase $deleteOpeningHourUseCase;

    public function __construct(
        OpeningHourRepositoryInterface $openingHourRepository,
        AddOpeningHourUseCase $addOpeningHourUseCase,
        DeleteOpeningHourUseCase $deleteOpeningHourUseCase
    ) {
        $this->openingHourRepository = $openingHourRepository;
        $this->addOpeningHourUseCase = $addOpeningHourUseCase;
        $this->deleteOpeningHourUseCase = $deleteOpeningHourUseCase;
    }

    public function addOpeningHour(int $parkingId, int $weekdayStart, int $weekdayEnd, string $openingTime, string $closingTime): OpeningHour
    {
        return $this->addOpeningHourUseCase->execute($parkingId, $weekdayStart, $weekdayEnd, $openingTime, $closingTime);
    }

    public function deleteOpeningHour(int $id): void
    {
        $this->deleteOpeningHourUseCase->execute($id);
    }

    public function getOpeningHoursByParkingId(int $parkingId): array
    {
        return $this->openingHourRepository->findByParkingId($parkingId);
    }
}


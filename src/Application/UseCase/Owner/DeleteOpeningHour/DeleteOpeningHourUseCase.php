<?php

namespace App\Application\UseCase\Owner\DeleteOpeningHour;

use App\Domain\Repository\OpeningHourRepositoryInterface;

class DeleteOpeningHourUseCase
{
    private OpeningHourRepositoryInterface $openingHourRepository;

    public function __construct(OpeningHourRepositoryInterface $openingHourRepository)
    {
        $this->openingHourRepository = $openingHourRepository;
    }

    public function execute(int $id): void
    {
        $this->openingHourRepository->delete($id);
    }
}

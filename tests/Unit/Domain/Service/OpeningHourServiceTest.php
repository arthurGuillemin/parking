<?php

namespace Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use App\Domain\Service\OpeningHourService;
use App\Domain\Repository\OpeningHourRepositoryInterface;
use App\Application\UseCase\Owner\AddOpeningHour\AddOpeningHourUseCase;
use App\Application\UseCase\Owner\DeleteOpeningHour\DeleteOpeningHourUseCase;
use App\Domain\Entity\OpeningHour;

class OpeningHourServiceTest extends TestCase
{
    public function testServiceCanBeInstantiated()
    {
        $openingHourRepository = $this->createMock(OpeningHourRepositoryInterface::class);
        $addUseCase = $this->createMock(AddOpeningHourUseCase::class);
        $deleteUseCase = $this->createMock(DeleteOpeningHourUseCase::class);

        $service = new OpeningHourService($openingHourRepository, $addUseCase, $deleteUseCase);
        $this->assertInstanceOf(OpeningHourService::class, $service);
    }
}

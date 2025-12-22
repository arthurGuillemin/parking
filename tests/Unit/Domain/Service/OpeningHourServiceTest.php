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
<<<<<<< HEAD
    public function testAddOpeningHourReturnsOpeningHour()
    {
        $openingHourRepository = $this->createStub(OpeningHourRepositoryInterface::class);
        $addOpeningHourUseCase = $this->createStub(AddOpeningHourUseCase::class);
        $deleteOpeningHourUseCase = $this->createStub(DeleteOpeningHourUseCase::class);

        $openingHour = $this->createStub(OpeningHour::class);
        $addOpeningHourUseCase->method('execute')->willReturn($openingHour);

        $service = new OpeningHourService($openingHourRepository, $addOpeningHourUseCase, $deleteOpeningHourUseCase);
        $result = $service->addOpeningHour(1, 1, 5, '08:00:00', '18:00:00');

        $this->assertSame($openingHour, $result);
=======
    public function testServiceCanBeInstantiated()
    {
        $openingHourRepository = $this->createMock(OpeningHourRepositoryInterface::class);
        $addUseCase = $this->createMock(AddOpeningHourUseCase::class);
        $deleteUseCase = $this->createMock(DeleteOpeningHourUseCase::class);

        $service = new OpeningHourService($openingHourRepository, $addUseCase, $deleteUseCase);
        $this->assertInstanceOf(OpeningHourService::class, $service);
>>>>>>> main
    }

    public function testGetOpeningHoursByParkingIdReturnsArray()
    {
        $openingHourRepository = $this->createStub(OpeningHourRepositoryInterface::class);
        $addOpeningHourUseCase = $this->createStub(AddOpeningHourUseCase::class);
        $deleteOpeningHourUseCase = $this->createStub(DeleteOpeningHourUseCase::class);

        $openingHourRepository->method('findByParkingId')->willReturn([]);

        $service = new OpeningHourService($openingHourRepository, $addOpeningHourUseCase, $deleteOpeningHourUseCase);
        $result = $service->getOpeningHoursByParkingId(1);

        $this->assertIsArray($result);
    }
}

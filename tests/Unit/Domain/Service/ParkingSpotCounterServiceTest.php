<?php

namespace Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use App\Domain\Service\ParkingSpotCounterService;
use App\Application\UseCase\Parking\CountAvailableParkingSpotsUseCase\CountAvailableParkingSpotsUseCase;
use App\Application\UseCase\Parking\CountAvailableParkingSpotsUseCase\CountAvailableParkingSpotsRequest;

class ParkingSpotCounterServiceTest extends TestCase
{
    public function testGetAvailableSpotsDelegatesToUseCase()
    {
        $useCase = $this->createMock(CountAvailableParkingSpotsUseCase::class);
        $useCase->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(CountAvailableParkingSpotsRequest::class))
            ->willReturn(5);
        $service = new ParkingSpotCounterService($useCase);
        $result = $service->getAvailableSpots(1, new \DateTimeImmutable());
        $this->assertEquals(5, $result);
    }
}

<?php

namespace Unit\Domain\Service;

use App\Application\DTO\Parking\CountAvailableParkingSpots\CountAvailableParkingSpotsRequest;
use App\Application\UseCase\Parking\CountAvailableParkingSpots\CountAvailableParkingSpotsUseCase;
use App\Domain\Service\ParkingSpotCounterService;
use PHPUnit\Framework\TestCase;

class ParkingSpotCounterServiceTest extends TestCase
{
    public function testGetAvailableSpotsDelegatesToUseCase()
    {
        $useCase = $this->createMock(CountAvailableParkingSpotsUseCase::class);
        $useCase->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(CountAvailableParkingSpotsRequest::class))
            ->willReturn(new \App\Application\DTO\Parking\CountAvailableParkingSpots\CountAvailableParkingSpotsResponse(1, 10, 5, new \DateTimeImmutable()));
        $service = new ParkingSpotCounterService($useCase);
        $result = $service->getAvailableSpots(1, new \DateTimeImmutable());
        $this->assertEquals(5, $result);
    }
}

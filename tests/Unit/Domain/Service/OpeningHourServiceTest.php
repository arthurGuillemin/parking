<?php

namespace Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use App\Domain\Service\OpeningHourService;
use App\Domain\Repository\OpeningHourRepositoryInterface;
use App\Application\UseCase\Owner\UpdateOpeningHour\UpdateOpeningHourRequest;
use App\Domain\Entity\OpeningHour;

class OpeningHourServiceTest extends TestCase
{
    public function testUpdateOpeningHourReturnsOpeningHour()
    {
        $openingHourRepository = $this->createMock(OpeningHourRepositoryInterface::class);
        $openingHour = $this->createMock(OpeningHour::class);
        $openingHourRepository->method('save')->willReturn($openingHour);
        $service = new OpeningHourService($openingHourRepository);
        $request = new UpdateOpeningHourRequest(1, 1, 1, '08:00:00', '18:00:00');
        $result = $service->updateOpeningHour($request);
        $this->assertSame($openingHour, $result);
    }
}

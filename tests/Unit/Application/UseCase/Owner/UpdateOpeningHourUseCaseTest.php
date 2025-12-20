<?php
namespace Unit\Application\UseCase\Owner;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\Owner\UpdateOpeningHour\UpdateOpeningHourUseCase;
use App\Application\UseCase\Owner\UpdateOpeningHour\UpdateOpeningHourRequest;
use App\Domain\Repository\OpeningHourRepositoryInterface;
use App\Domain\Entity\OpeningHour;

class UpdateOpeningHourUseCaseTest extends TestCase
{
    public function testExecuteReturnsSavedOpeningHour()
    {
        $repo = $this->createMock(OpeningHourRepositoryInterface::class);
        $openingHour = $this->createMock(OpeningHour::class);
        $repo->method('save')->willReturn($openingHour);
        $useCase = new UpdateOpeningHourUseCase($repo);
        $request = new UpdateOpeningHourRequest(1, 1, 5, '08:00:00', '18:00:00');
        $result = $useCase->execute($request);
        $this->assertSame($openingHour, $result);
    }
}

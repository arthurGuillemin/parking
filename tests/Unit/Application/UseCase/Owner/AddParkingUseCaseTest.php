<?php
namespace Unit\Application\UseCase\Owner;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\Owner\AddParking\AddParkingUseCase;
use App\Application\UseCase\Owner\AddParking\AddParkingRequest;
use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Entity\Parking;

class AddParkingUseCaseTest extends TestCase
{
    public function testExecuteReturnsSavedParking()
    {
        $repo = $this->createMock(ParkingRepositoryInterface::class);
        $parking = $this->createMock(Parking::class);
        $repo->expects($this->once())
            ->method('save')
            ->willReturn($parking);
        $useCase = new AddParkingUseCase($repo);
        $request = new AddParkingRequest(1, 'Parking', 'Address', 1.0, 2.0, 10, true);
        $result = $useCase->execute($request);
        $this->assertSame($parking, $result);
    }
}


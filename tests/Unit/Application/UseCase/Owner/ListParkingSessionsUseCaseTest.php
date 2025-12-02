<?php
namespace Unit\Application\UseCase\Owner;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\Owner\ListParkingSessions\ListParkingSessionsUseCase;
use App\Application\UseCase\Owner\ListParkingSessions\ListParkingSessionsRequest;
use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Entity\ParkingSession;

class ListParkingSessionsUseCaseTest extends TestCase
{
    public function testExecuteReturnsSessions()
    {
        $repo = $this->createMock(ParkingSessionRepositoryInterface::class);
        $session = $this->createMock(ParkingSession::class);
        $repo->method('findByParkingId')->willReturn([$session]);
        $useCase = new ListParkingSessionsUseCase($repo);
        $request = new ListParkingSessionsRequest(1);
        $result = $useCase->execute($request);
        $this->assertIsArray($result);
        $this->assertSame($session, $result[0]);
    }
}


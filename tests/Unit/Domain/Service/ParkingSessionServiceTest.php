<?php
namespace Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use App\Domain\Service\ParkingSessionService;
use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Application\UseCase\Owner\ListParkingSessions\ListParkingSessionsRequest;

class ParkingSessionServiceTest extends TestCase
{
    public function testListParkingSessionsDelegatesToUseCase()
    {
        $sessionRepo = $this->createMock(ParkingSessionRepositoryInterface::class);
        $service = new ParkingSessionService($sessionRepo);
        $request = new ListParkingSessionsRequest(1);
        $result = $service->listParkingSessions($request);
        $this->assertIsArray($result);
    }
}

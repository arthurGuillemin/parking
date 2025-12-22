<?php
namespace Unit\Interface\Controller;

use PHPUnit\Framework\TestCase;
use App\Interface\Controller\ParkingController;
use App\Domain\Service\ParkingService;
use App\Domain\Entity\Parking;

class ParkingControllerTest extends TestCase
{
    public function testAddReturnsJson()
    {
        $mockService = $this->createMock(ParkingService::class);
        $mockJwt = $this->createMock(\App\Domain\Service\JwtService::class);
        $mockXss = $this->createMock(\App\Domain\Security\XssProtectionService::class);

        $mockParking = $this->createMock(Parking::class);
        $mockParking->method('getParkingId')->willReturn(1);
        $mockParking->method('getOwnerId')->willReturn('owner');
        $mockParking->method('getName')->willReturn('name');
        $mockParking->method('getAddress')->willReturn('address');
        // Other getters not used in echo json_encode map in controller currently?
        // Controller map: id, ownerId, name, address.

        $mockService->method('addParking')->willReturn($mockParking);

        $mockXss->method('sanitize')->willReturnCallback(fn($arg) => $arg);

        $controller = new ParkingController($mockService, $mockJwt, $mockXss);
        $data = [
            'ownerId' => 'owner',
            'name' => 'name',
            'address' => 'address',
            'latitude' => 1.0,
            'longitude' => 2.0,
            'totalCapacity' => 10,
            'open_24_7' => true
        ];

        ob_start();
        $controller->add($data);
        $output = ob_get_clean();

        $result = json_decode($output, true);

        $this->assertEquals([
            'id' => 1,
            'ownerId' => 'owner',
            'name' => 'name',
            'address' => 'address',
        ], $result);
    }

    public function testAddThrowsOnMissingFields()
    {
        // Controller throws InvalidArgumentException which is caught? 
        // No, catch block catches Throwable but typically we want to test the Throw if checking logic BEFORE catch?
        // Wait, Controller catches Throwable and echos error JSON 500.
        // BUT it throws InvalidArgumentException inside try.
        // If it catches it, it won't bubble up.
        // So I should expect JSON output with error?

        // Actually, Controller `add` catches `Throwable`.
        // So `testAddThrowsOnMissingFields` expecting Exception will FAIL if Controller catches it!
        // I should assert ERROR JSON response.

        $mockService = $this->createMock(ParkingService::class);
        $mockJwt = $this->createMock(\App\Domain\Service\JwtService::class);
        $mockXss = $this->createMock(\App\Domain\Security\XssProtectionService::class);

        $controller = new ParkingController($mockService, $mockJwt, $mockXss);

        // Missing ownerId
        ob_start();
        $controller->add([]);
        $output = ob_get_clean();

        $result = json_decode($output, true);

        // Validation check in controller throws InvalidArgumentException "Champs requis manquants: ownerId"
        // Catch block logs error and outputs json ['error' => 'Erreur serveur: Champs requis manquants: ownerId']

        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('Champs requis manquants: ownerId', $result['error']);
    }
}


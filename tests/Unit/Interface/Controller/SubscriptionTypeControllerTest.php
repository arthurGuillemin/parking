<?php
namespace Unit\Interface\Controller;

use PHPUnit\Framework\TestCase;
use App\Interface\Controller\SubscriptionTypeController;
use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeUseCase;
use App\Interface\Presenter\SubscriptionTypePresenter;
use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeResponse;
use App\Application\UseCase\Owner\ListSubscriptionTypes\ListSubscriptionTypesUseCase;
use App\Application\UseCase\Owner\GetSubscriptionType\GetSubscriptionTypeUseCase;

class SubscriptionTypeControllerTest extends TestCase
{
    public function testAddReturnsArray()
    {
        $mockUseCase = $this->createMock(AddSubscriptionTypeUseCase::class);
        $mockPresenter = $this->createMock(SubscriptionTypePresenter::class);
        $mockResponse = new AddSubscriptionTypeResponse(1, 2, 'Annual', 'desc');
        $mockUseCase->method('execute')->willReturn($mockResponse);
        $mockPresenter->method('present')->willReturn([
            'id' => 1,
            'parkingId' => 2,
            'name' => 'Annual',
            'description' => 'desc',
        ]);
        $controller = new SubscriptionTypeController($mockUseCase, $this->createMock(ListSubscriptionTypesUseCase::class), $this->createMock(GetSubscriptionTypeUseCase::class), $mockPresenter);
        $data = [
            'parkingId' => 2,
            'name' => 'Annual',
            'description' => 'desc'
        ];
        $result = $controller->add($data);
        $this->assertEquals([
            'id' => 1,
            'parkingId' => 2,
            'name' => 'Annual',
            'description' => 'desc',
        ], $result);
    }
    public function testAddThrowsOnMissingFields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $mockUseCase = $this->createMock(AddSubscriptionTypeUseCase::class);
        $mockPresenter = $this->createMock(SubscriptionTypePresenter::class);
        $controller = new SubscriptionTypeController($mockUseCase, $this->createMock(ListSubscriptionTypesUseCase::class), $this->createMock(GetSubscriptionTypeUseCase::class), $mockPresenter);
        $controller->add(['parkingId' => 2]);
    }
}

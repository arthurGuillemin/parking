<?php

namespace Unit\Interface\Controller;

use App\Application\UseCase\Owner\GetMonthlyRevenue\GetMonthlyRevenueRequest;
use App\Domain\Service\MonthlyRevenueService;
use App\Interface\Controller\MonthlyRevenueController;
use PHPUnit\Framework\TestCase;

class MonthlyRevenueControllerTest extends TestCase
{
    public function testGetThrowsOnMissingFields()
    {
        $controller = new MonthlyRevenueController($this->createMock(MonthlyRevenueService::class));
        $this->expectException(\InvalidArgumentException::class);
        $controller->get(['parkingId' => 1, 'year' => 2025]);
    }

    public function testGetReturnsRevenueArray()
    {
        $mockService = $this->createMock(MonthlyRevenueService::class);
        $mockService->method('getMonthlyRevenue')->willReturn([
            'total' => 123.45,
            'reservations' => 100.0,
            'subscriptions' => 23.45
        ]);
        $controller = new MonthlyRevenueController($mockService);
        $data = ['parkingId' => 1, 'year' => 2025, 'month' => 11];
        $result = $controller->get($data);
        $this->assertEquals([
            'parkingId' => 1,
            'year' => 2025,
            'month' => 11,
            'revenue' => 123.45,
            'breakdown' => [
                'reservations' => 100.0,
                'subscriptions' => 23.45
            ]
        ], $result);
    }
}

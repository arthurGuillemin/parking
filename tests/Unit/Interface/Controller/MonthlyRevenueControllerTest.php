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
        $mockService->method('getMonthlyRevenue')->willReturn(123.45);
        $controller = new MonthlyRevenueController($mockService);
        $data = ['parkingId' => 1, 'year' => 2025, 'month' => 11];
        $result = $controller->get($data);
        $this->assertEquals([
            'revenue' => 123.45,
            'month' => 11,
            'year' => 2025,
            'parkingId' => 1,
        ], $result);
    }
}

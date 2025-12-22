<?php

namespace Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use App\Domain\Service\MonthlyRevenueService;
use App\Domain\Repository\InvoiceRepositoryInterface;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Application\UseCase\Owner\GetMonthlyRevenue\GetMonthlyRevenueRequest;

class MonthlyRevenueServiceTest extends TestCase
{
    public function testGetMonthlyRevenueReturnsArray()
    {
        $invoiceRepository = $this->createStub(InvoiceRepositoryInterface::class);
        $subscriptionRepository = $this->createStub(SubscriptionRepositoryInterface::class);
        $invoiceRepository->method('findByParkingIdAndDateRange')->willReturn([]);
        $subscriptionRepository->method('findByParkingIdAndMonth')->willReturn([]);

        $service = new MonthlyRevenueService($invoiceRepository, $subscriptionRepository);
        $request = new GetMonthlyRevenueRequest(1, 2025, 11);

<<<<<<< HEAD
        $result = $service->getMonthlyRevenue($request);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('reservations', $result);
        $this->assertArrayHasKey('subscriptions', $result);
=======
        $this->assertIsArray($service->getMonthlyRevenue($request));
>>>>>>> main
    }
}

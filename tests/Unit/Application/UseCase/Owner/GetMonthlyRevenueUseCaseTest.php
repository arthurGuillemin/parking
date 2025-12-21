<?php
namespace Unit\Application\UseCase\Owner;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\Owner\GetMonthlyRevenue\GetMonthlyRevenueUseCase;
use App\Application\UseCase\Owner\GetMonthlyRevenue\GetMonthlyRevenueRequest;
use App\Domain\Repository\InvoiceRepositoryInterface;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Domain\Entity\Invoice;
use App\Domain\Entity\Subscription;

class GetMonthlyRevenueUseCaseTest extends TestCase
{
    public function testExecuteReturnsSumOfInvoicesAndSubscriptions()
    {
        $invoiceRepo = $this->createMock(InvoiceRepositoryInterface::class);
        $subscriptionRepo = $this->createMock(SubscriptionRepositoryInterface::class);
        $invoice = $this->createMock(Invoice::class);
        $invoice->method('getAmountTtc')->willReturn(100.0);
        $invoiceRepo->method('findByParkingIdAndDateRange')->willReturn([$invoice]);
        $subscription = $this->createMock(Subscription::class);
        $subscription->method('getMonthlyPrice')->willReturn(50.0);
        $subscriptionRepo->method('findByParkingIdAndMonth')->willReturn([$subscription]);
        $useCase = new GetMonthlyRevenueUseCase($invoiceRepo, $subscriptionRepo);
        $request = new GetMonthlyRevenueRequest(1, 2025, 11);
        $result = $useCase->execute($request);
        $this->assertEquals(150.0, $result['total']);
    }
}


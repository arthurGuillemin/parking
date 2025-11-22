<?php

namespace App\Domain\Service;

use App\Domain\Repository\InvoiceRepositoryInterface;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Application\UseCase\Owner\GetMonthlyRevenue\GetMonthlyRevenueUseCase;
use App\Application\UseCase\Owner\GetMonthlyRevenue\GetMonthlyRevenueRequest;

class MonthlyRevenueService
{
    private GetMonthlyRevenueUseCase $getMonthlyRevenueUseCase;

    public function __construct(InvoiceRepositoryInterface $invoiceRepository, SubscriptionRepositoryInterface $subscriptionRepository)
    {
        $this->getMonthlyRevenueUseCase = new GetMonthlyRevenueUseCase($invoiceRepository, $subscriptionRepository);
    }

    public function getMonthlyRevenue(GetMonthlyRevenueRequest $request): float
    {
        return $this->getMonthlyRevenueUseCase->execute($request);
    }
}


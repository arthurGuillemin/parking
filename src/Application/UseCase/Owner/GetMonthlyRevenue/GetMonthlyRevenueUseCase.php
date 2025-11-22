<?php

namespace App\Application\UseCase\Owner\GetMonthlyRevenue;

use App\Domain\Repository\InvoiceRepositoryInterface;
use App\Domain\Repository\SubscriptionRepositoryInterface;

class GetMonthlyRevenueUseCase
{
    private InvoiceRepositoryInterface $invoiceRepository;
    private SubscriptionRepositoryInterface $subscriptionRepository;

    public function __construct(InvoiceRepositoryInterface $invoiceRepository, SubscriptionRepositoryInterface $subscriptionRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * Get the monthly revenue for a parking (reservations + subscriptions).
     *
     * @param GetMonthlyRevenueRequest $request
     * @return float
     */
    public function execute(GetMonthlyRevenueRequest $request): float
    {
        $start = new \DateTimeImmutable(sprintf('%04d-%02d-01 00:00:00', $request->year, $request->month));
        $end = $start->modify('+1 month');
        $total = 0.0;

        // 1. Additionner les factures de réservation et de session du mois
        $invoices = $this->invoiceRepository->findByParkingIdAndDateRange($request->parkingId, $start, $end);
        foreach ($invoices as $invoice) {
            $total += $invoice->getAmountTtc();
        }

        // 2. Additionner les abonnements actifs sur la période
        $subscriptions = $this->subscriptionRepository->findByParkingIdAndMonth($request->parkingId, $request->year, $request->month);
        foreach ($subscriptions as $subscription) {
            $total += $subscription->getMonthlyPrice();
        }

        return $total;
    }
}


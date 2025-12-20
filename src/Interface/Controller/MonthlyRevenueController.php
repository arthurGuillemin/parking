<?php

namespace App\Interface\Controller;

use App\Domain\Service\MonthlyRevenueService;
use App\Application\UseCase\Owner\GetMonthlyRevenue\GetMonthlyRevenueRequest;
use Exception;

class MonthlyRevenueController
{
    private MonthlyRevenueService $monthlyRevenueService;

    public function __construct(MonthlyRevenueService $monthlyRevenueService)
    {
        $this->monthlyRevenueService = $monthlyRevenueService;
    }

    public function get(array $data): array
    {
        if (empty($data['parkingId']) || empty($data['year']) || empty($data['month'])) {
            throw new \InvalidArgumentException('Les champs sont obligatoires.');
        }
        $request = new GetMonthlyRevenueRequest((int) $data['parkingId'], (int) $data['year'], (int) $data['month']);
        $revenue = $this->monthlyRevenueService->getMonthlyRevenue($request);
        return [
            'parkingId' => $data['parkingId'],
            'year' => $data['year'],
            'month' => $data['month'],
            'revenue' => $revenue['total'],
            'breakdown' => [
                'reservations' => $revenue['reservations'],
                'subscriptions' => $revenue['subscriptions']
            ]
        ];
    }
}


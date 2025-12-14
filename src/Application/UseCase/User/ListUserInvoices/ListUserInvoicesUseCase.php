<?php

namespace App\Application\UseCase\User\ListUserInvoices;

use App\Domain\Repository\InvoiceRepositoryInterface;
use App\Application\DTO\Response\InvoiceResponse;

class ListUserInvoicesUseCase
{
    private InvoiceRepositoryInterface $invoiceRepository;

    public function __construct(InvoiceRepositoryInterface $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    public function execute(ListUserInvoicesRequest $request): array
    {
        $invoices = $this->invoiceRepository->findByUserId($request->userId);

        return array_map(function ($invoice) {
            return new InvoiceResponse(
                $invoice->getInvoiceId(),
                $invoice->getIssuedDate()->format('d/m/Y'),
                $invoice->getAmountTtc(),
                $invoice->getInvoiceType()
            );
        }, $invoices);
    }
}

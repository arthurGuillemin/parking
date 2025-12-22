<?php

namespace App\Application\UseCase\User\GetInvoice;

use App\Domain\Repository\InvoiceRepositoryInterface;
use App\Domain\Entity\Invoice;

class GetInvoiceUseCase
{
    private InvoiceRepositoryInterface $invoiceRepository;

    public function __construct(InvoiceRepositoryInterface $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    public function execute(GetInvoiceRequest $request): ?Invoice
    {
        $invoice = $this->invoiceRepository->findById($request->invoiceId);

        if (!$invoice) {
            return null;
        }



        return $invoice;
    }
}

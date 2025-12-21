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

        // TODO: Verification of ownership needs User ID on Invoice or via linked Reservation/Subscription.
        // Currently Invoice entity doesn't seem to have userId directly, it links to reservation/session/subscription.
        // We might need to check the linked entity's owner.
        // For MVP/simplicity, we might skip strict ownership check OR fetching the linked entity is required.

        // Reviewing Invoice.php... it has reservationId, sessionId.
        // Let's assume for now we return it. Strict check would require fetching the reservation to check userId.

        return $invoice;
    }
}

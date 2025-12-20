<?php

namespace Unit\Domain\Entity;

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\Invoice;

class InvoiceTest extends TestCase
{
    public function testGetters()
    {
        $details = ['foo' => 'bar'];
        $detailsJson = json_encode($details);
        $invoice = new Invoice(1, 2, 3, new \DateTimeImmutable('2025-11-28'), 10.0, 12.0, $detailsJson, 'reservation');
        $this->assertEquals(1, $invoice->getInvoiceId());
        $this->assertEquals(2, $invoice->getReservationId());
        $this->assertEquals(3, $invoice->getSessionId());
        $this->assertEquals(new \DateTimeImmutable('2025-11-28'), $invoice->getIssuedDate());
        $this->assertEquals(10.0, $invoice->getAmountHt());
        $this->assertEquals(12.0, $invoice->getAmountTtc());
        $this->assertEquals($details, $invoice->getDetailsJson());
        $this->assertEquals('reservation', $invoice->getInvoiceType());
    }
}

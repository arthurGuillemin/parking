<?php
namespace Unit\Interface\Controller;

use PHPUnit\Framework\TestCase;
use App\Interface\Controller\PricingRuleController;
use App\Domain\Service\PricingRuleService;
use App\Application\UseCase\Owner\UpdatePricingRule\UpdatePricingRuleRequest;
use App\Domain\Entity\PricingRule;

class PricingRuleControllerTest extends TestCase
{
    public function testUpdateReturnsArray()
    {
        $mockService = $this->createMock(PricingRuleService::class);
        $mockRule = $this->createMock(PricingRule::class);
        $mockRule->method('getPricingRuleId')->willReturn(1);
        $mockRule->method('getParkingId')->willReturn(2);
        $mockRule->method('getStartDurationMinute')->willReturn(10);
        $mockRule->method('getEndDurationMinute')->willReturn(20);
        $mockRule->method('getPricePerSlice')->willReturn(2.5);
        $mockRule->method('getSliceInMinutes')->willReturn(15);
        $mockRule->method('getEffectiveDate')->willReturn(new \DateTimeImmutable('2025-11-29'));
        $mockService->method('updatePricingRule')->willReturn($mockRule);
        $controller = new PricingRuleController($mockService);
        $data = [
            'parkingId' => 2,
            'startDurationMinute' => 10,
            'endDurationMinute' => 20,
            'pricePerSlice' => 2.5,
            'sliceInMinutes' => 15,
            'effectiveDate' => '2025-11-29'
        ];
        $result = $controller->update($data);
        $this->assertEquals([
            'id' => 1,
            'parkingId' => 2,
            'startDurationMinute' => 10,
            'endDurationMinute' => 20,
            'pricePerSlice' => 2.5,
            'sliceInMinutes' => 15,
            'effectiveDate' => '2025-11-29',
        ], $result);
    }
    public function testUpdateThrowsOnMissingFields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $controller = new PricingRuleController($this->createMock(PricingRuleService::class));
        $controller->update(['parkingId' => 2]);
    }
}


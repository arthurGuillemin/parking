<?php

namespace Unit\Domain\Service;

use App\Domain\Entity\PricingRule;
use App\Application\UseCase\Owner\UpdatePricingRule\UpdatePricingRuleRequest;
use App\Application\UseCase\Owner\UpdatePricingRule\UpdatePricingRuleUseCase;
use App\Domain\Repository\PricingRuleRepositoryInterface;
use App\Domain\Service\PricingRuleService;
use PHPUnit\Framework\TestCase;

class PricingRuleServiceTest extends TestCase
{
    public function testConstructor()
    {
        $repo = $this->createMock(PricingRuleRepositoryInterface::class);
        $service = new PricingRuleService($repo);
        $this->assertInstanceOf(PricingRuleService::class, $service);
    }

    public function testUpdatePricingRuleReturnsPricingRule()
    {
        $repo = $this->createMock(PricingRuleRepositoryInterface::class);
        $service = new PricingRuleService($repo);

        $mockUseCase = $this->getMockBuilder(UpdatePricingRuleUseCase::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['execute'])
            ->getMock();

        $mockPricingRule = $this->createMock(PricingRule::class);
        $mockUseCase->method('execute')->willReturn($mockPricingRule);

        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('updatePricingRuleUseCase');
        $property->setAccessible(true);
        $property->setValue($service, $mockUseCase);

        $mockRequest = $this->createMock(UpdatePricingRuleRequest::class);
        $result = $service->updatePricingRule($mockRequest);

        $this->assertInstanceOf(PricingRule::class, $result);
    }
}

<?php
namespace Unit\Application\UseCase\Owner;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\Owner\UpdatePricingRule\UpdatePricingRuleUseCase;
use App\Application\UseCase\Owner\UpdatePricingRule\UpdatePricingRuleRequest;
use App\Domain\Repository\PricingRuleRepositoryInterface;
use App\Domain\Entity\PricingRule;

class UpdatePricingRuleUseCaseTest extends TestCase
{
    public function testExecuteReturnsSavedPricingRule()
    {
        $repo = $this->createMock(PricingRuleRepositoryInterface::class);
        $pricingRule = $this->createMock(PricingRule::class);
        $repo->method('save')->willReturn($pricingRule);
        $useCase = new UpdatePricingRuleUseCase($repo);
        $request = new UpdatePricingRuleRequest(1, 0, 60, 2.5, 15, new \DateTimeImmutable('2025-11-28'));
        $result = $useCase->execute($request);
        $this->assertSame($pricingRule, $result);
    }
}


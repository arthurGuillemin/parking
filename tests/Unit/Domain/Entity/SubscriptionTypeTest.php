<?php

namespace Tests\Unit\Domain\Entity;

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\SubscriptionType;

class SubscriptionTypeTest extends TestCase
{
    private SubscriptionType $type;
    private int $parkingId = 1;
    private string $name = 'Weekend Access';
    private ?string $description = 'Friday 6PM to Monday 10AM';

    protected function setUp(): void
    {
        $this->type = new SubscriptionType(
            1,
            $this->parkingId,
            $this->name,
            $this->description
        );
    }

    public function testGetSubscriptionTypeId(): void
    {
        $this->assertEquals(1, $this->type->getSubscriptionTypeId());
    }

    public function testGetParkingId(): void
    {
        $this->assertEquals($this->parkingId, $this->type->getParkingId());
    }

    public function testGetName(): void
    {
        $this->assertEquals($this->name, $this->type->getName());
    }

    public function testGetDescription(): void
    {
        $this->assertEquals($this->description, $this->type->getDescription());
    }

    public function testGetDescriptionCanBeNull(): void
    {
        $type = new SubscriptionType(1, $this->parkingId, $this->name, null);
        $this->assertNull($type->getDescription());
    }
}
<?php

namespace Unit\Domain\Entity;

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\Owner;

class OwnerTest extends TestCase
{
    public function testGetters()
    {
        $owner = new Owner('uuid', 'email@example.com', 'hash', 'Lebron', 'James', new \DateTimeImmutable('2025-11-28 10:00:00'));
        $this->assertEquals('uuid', $owner->getOwnerId());
        $this->assertEquals('email@example.com', $owner->getEmail());
        $this->assertEquals('hash', $owner->getPassword());
        $this->assertEquals('Lebron', $owner->getFirstName());
        $this->assertEquals('James', $owner->getLastName());
        $this->assertEquals(new \DateTimeImmutable('2025-11-28 10:00:00'), $owner->getCreationDate());
    }
}

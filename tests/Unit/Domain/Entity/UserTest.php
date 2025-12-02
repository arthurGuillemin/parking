<?php

namespace Unit\Domain\Entity;

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\User;

class UserTest extends TestCase
{
    public function testGetters()
    {$date = new \DateTimeImmutable('2025-11-28');
        $user = new User('uuid', 'email@example.com', 'hash', 'Lebron', 'James', $date);
        $this->assertEquals('uuid', $user->getUserId());
        $this->assertEquals('email@example.com', $user->getEmail());
        $this->assertEquals('hash', $user->getPassword());
        $this->assertEquals('Lebron', $user->getFirstName());
        $this->assertEquals('James', $user->getLastName());
        $this->assertEquals($date, $user->getCreationDate());
    }

    public function testEdgeCases()
    {
        $date = new \DateTimeImmutable('2000-01-01');
        $user = new User('', '', '', '', '', $date);
        $this->assertEquals('', $user->getUserId());
        $this->assertEquals('', $user->getEmail());
        $this->assertEquals('', $user->getPassword());
        $this->assertEquals('', $user->getFirstName());
        $this->assertEquals('', $user->getLastName());
        $this->assertEquals($date, $user->getCreationDate());
    }
}

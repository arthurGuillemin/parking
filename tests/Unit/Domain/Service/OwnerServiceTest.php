<?php

namespace Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use App\Domain\Service\OwnerService;
use App\Domain\Repository\OwnerRepositoryInterface;
use App\Domain\Entity\Owner;

class OwnerServiceTest extends TestCase
{
    public function testRegisterReturnsOwner()
    {
        $ownerRepository = $this->createMock(OwnerRepositoryInterface::class);
        $owner = $this->createMock(Owner::class);
        $ownerRepository->method('save')->willReturn($owner);
        $service = new OwnerService($ownerRepository);
        $result = $service->register('James@example.com', 'password', 'first', 'last');
        $this->assertSame($owner, $result);
    }

    public function testAuthenticateReturnsOwnerOrNull()
    {
        $ownerRepository = $this->createMock(OwnerRepositoryInterface::class);
        $owner = $this->createMock(Owner::class);
        $ownerRepository->method('findByEmail')->willReturn($owner);
        $service = new OwnerService($ownerRepository);
        $result = $service->authenticate('email', 'password');
        $this->assertTrue($result === $owner || $result === null);
    }
}

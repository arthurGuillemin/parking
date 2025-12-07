<?php
namespace Unit\Application\UseCase;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\LoginUseCase;
use App\Application\DTO\LoginResponse;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Repository\OwnerRepositoryInterface;
use App\Domain\Auth\TokenGeneratorInterface;

class LoginUseCaseTest extends TestCase
{
    public function testAuthenticateOwnerSuccess()
    {
        $mockUserRepo = $this->createMock(UserRepositoryInterface::class);
        $mockOwnerRepo = $this->createMock(OwnerRepositoryInterface::class);
        $mockTokenGen = $this->createMock(TokenGeneratorInterface::class);
        $mockOwner = $this->createMock(\App\Domain\Entity\Owner::class);
        $mockOwner->method('getOwnerId')->willReturn('42');
        $mockOwner->method('getEmail')->willReturn('owner@example.com');
        $mockOwner->method('getPassword')->willReturn(password_hash('ownerpass', PASSWORD_DEFAULT));
        $mockOwnerRepo->method('findByEmail')->willReturn($mockOwner);
        $mockUserRepo->method('findByEmail')->willReturn(null);
        $mockTokenGen->expects($this->exactly(2))
            ->method('generate')
            ->willReturnCallback(function($payload) {
                if ($payload['type'] === 'access') {
                    $this->assertEquals('owner', $payload['role']);
                    $this->assertEquals('42', $payload['user_id']);
                    return 'owner.jwt.token';
                } else {
                    $this->assertEquals('owner', $payload['role']);
                    $this->assertEquals('42', $payload['user_id']);
                    return 'owner.refresh.token';
                }
            });
        $useCase = new LoginUseCase($mockUserRepo, $mockOwnerRepo, $mockTokenGen);
        $response = $useCase->execute('owner@example.com', 'ownerpass');
        $this->assertInstanceOf(LoginResponse::class, $response);
        $this->assertEquals('owner.jwt.token', $response->token);
    }

    public function testAuthenticateUserSuccess()
    {
        $mockUserRepo = $this->createMock(UserRepositoryInterface::class);
        $mockOwnerRepo = $this->createMock(OwnerRepositoryInterface::class);
        $mockTokenGen = $this->createMock(TokenGeneratorInterface::class);
        $mockUser = $this->createMock(\App\Domain\Entity\User::class);
        $mockUser->method('getUserId')->willReturn('7');
        $mockUser->method('getEmail')->willReturn('user@example.com');
        $mockUser->method('getPassword')->willReturn(password_hash('userpass', PASSWORD_DEFAULT));
        $mockOwnerRepo->method('findByEmail')->willReturn(null);
        $mockUserRepo->method('findByEmail')->willReturn($mockUser);
        $mockTokenGen->expects($this->exactly(2))
            ->method('generate')
            ->willReturnCallback(function($payload) {
                if ($payload['type'] === 'access') {
                    $this->assertEquals('user', $payload['role']);
                    $this->assertEquals('7', $payload['user_id']);
                    return 'user.jwt.token';
                } else {
                    $this->assertEquals('user', $payload['role']);
                    $this->assertEquals('7', $payload['user_id']);
                    return 'user.refresh.token';
                }
            });
        $useCase = new LoginUseCase($mockUserRepo, $mockOwnerRepo, $mockTokenGen);
        $response = $useCase->execute('user@example.com', 'userpass');
        $this->assertInstanceOf(LoginResponse::class, $response);
        $this->assertEquals('user.jwt.token', $response->token);
    }

    public function testAuthenticationFailsWithWrongPassword()
    {
        $mockUserRepo = $this->createMock(UserRepositoryInterface::class);
        $mockOwnerRepo = $this->createMock(OwnerRepositoryInterface::class);
        $mockTokenGen = $this->createMock(TokenGeneratorInterface::class);
        $mockUser = $this->createMock(\App\Domain\Entity\User::class);
        $mockUser->method('getUserId')->willReturn('7');
        $mockUser->method('getEmail')->willReturn('user@example.com');
        $mockUser->method('getPassword')->willReturn(password_hash('userpass', PASSWORD_DEFAULT));
        $mockOwnerRepo->method('findByEmail')->willReturn(null);
        $mockUserRepo->method('findByEmail')->willReturn($mockUser);
        $mockTokenGen->expects($this->never())->method('generate');
        $useCase = new LoginUseCase($mockUserRepo, $mockOwnerRepo, $mockTokenGen);
        $response = $useCase->execute('user@example.com', 'wrongpass');
        $this->assertNull($response);
    }

    public function testAuthenticationFailsWithUnknownEmail()
    {
        $mockUserRepo = $this->createMock(UserRepositoryInterface::class);
        $mockOwnerRepo = $this->createMock(OwnerRepositoryInterface::class);
        $mockTokenGen = $this->createMock(TokenGeneratorInterface::class);
        $mockOwnerRepo->method('findByEmail')->willReturn(null);
        $mockUserRepo->method('findByEmail')->willReturn(null);
        $mockTokenGen->expects($this->never())->method('generate');
        $useCase = new LoginUseCase($mockUserRepo, $mockOwnerRepo, $mockTokenGen);
        $response = $useCase->execute('unknown@example.com', 'nopass');
        $this->assertNull($response);
    }
}

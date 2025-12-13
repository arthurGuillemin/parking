<?php

namespace App\Infrastructure\Container;

use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Repository\OwnerRepositoryInterface;
use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Domain\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Repository\InvoiceRepositoryInterface;
use App\Domain\Repository\OpeningHourRepositoryInterface;
use App\Domain\Repository\PricingRuleRepositoryInterface;
use App\Domain\Repository\SubscriptionTypeRepositoryInterface;
use App\Domain\Repository\SubscriptionSlotRepositoryInterface;
use App\Infrastructure\Persistence\Sql\SqlUserRepository;
use App\Infrastructure\Persistence\Sql\SqlOwnerRepository;
use App\Infrastructure\Persistence\Sql\SqlParkingRepository;
use App\Infrastructure\Persistence\Sql\SqlReservationRepository;
use App\Infrastructure\Persistence\Sql\SqlSubscriptionRepository;
use App\Infrastructure\Persistence\Sql\SqlParkingSessionRepository;
use App\Infrastructure\Persistence\Sql\SqlInvoiceRepository;
use App\Infrastructure\Persistence\Sql\SqlOpeningHourRepository;
use App\Infrastructure\Persistence\Sql\SqlPricingRuleRepository;
use App\Infrastructure\Persistence\Sql\SqlSubscriptionTypeRepository;
use App\Infrastructure\Persistence\Sql\SqlSubscriptionSlotRepository;
use App\Infrastructure\Database\Database;
use App\Domain\Service\JwtService;
use App\Domain\Security\PasswordHasherInterface;
use App\Infrastructure\Security\PasswordHasher;
use App\Domain\Security\XssProtectionService;
use App\Application\UseCase\LoginUseCase;
use App\Application\UseCase\User\Register\UserRegisterUseCase;
use App\Domain\Service\UserRegistrationValidator;
use App\Interface\Controller\AuthController;
use App\Interface\Controller\RegisterController;
use App\Interface\Controller\RefreshTokenController;
use App\Interface\Controller\OwnerController;
use App\Domain\Service\OwnerService;
use Psr\Container\ContainerInterface;

class ServiceContainer implements ContainerInterface
{
    private array $services = [];
    private array $factories = [];

    public function __construct()
    {
        $this->registerServices();
    }

    private function registerServices(): void
    {
        // Database
        $this->factories['db'] = fn() => Database::getInstance();

        // Repositories
        $this->factories[UserRepositoryInterface::class] = fn() => new SqlUserRepository();
        $this->factories[OwnerRepositoryInterface::class] = fn() => new SqlOwnerRepository();
        $this->factories[ParkingRepositoryInterface::class] = fn() => new SqlParkingRepository();
        $this->factories[ReservationRepositoryInterface::class] = fn() => new SqlReservationRepository();
        $this->factories[SubscriptionRepositoryInterface::class] = fn() => new SqlSubscriptionRepository();
        $this->factories[ParkingSessionRepositoryInterface::class] = fn() => new SqlParkingSessionRepository();
        $this->factories[InvoiceRepositoryInterface::class] = fn() => new SqlInvoiceRepository();
        $this->factories[OpeningHourRepositoryInterface::class] = fn() => new SqlOpeningHourRepository();
        $this->factories[PricingRuleRepositoryInterface::class] = fn() => new SqlPricingRuleRepository();
        $this->factories[SubscriptionTypeRepositoryInterface::class] = fn() => new SqlSubscriptionTypeRepository();
        $this->factories[SubscriptionSlotRepositoryInterface::class] = fn() => new SqlSubscriptionSlotRepository();

        // Services
        $this->factories[JwtService::class] = fn() => new JwtService();
        $this->factories[PasswordHasherInterface::class] = fn() => new PasswordHasher();
        $this->factories[XssProtectionService::class] = fn() => new XssProtectionService();
        $this->factories[UserRegistrationValidator::class] = fn() => new UserRegistrationValidator();

        // Use Cases
        $this->factories[LoginUseCase::class] = function() {
            return new LoginUseCase(
                $this->get(UserRepositoryInterface::class),
                $this->get(OwnerRepositoryInterface::class),
                $this->get(JwtService::class),
                $this->get(PasswordHasherInterface::class)
            );
        };

        $this->factories[UserRegisterUseCase::class] = function() {
            return new UserRegisterUseCase(
                $this->get(UserRepositoryInterface::class),
                $this->get(UserRegistrationValidator::class),
                $this->get(PasswordHasherInterface::class)
            );
        };

        // Services métier
        $this->factories[OwnerService::class] = function() {
            return new OwnerService($this->get(OwnerRepositoryInterface::class));
        };

        // Controllers
        $this->factories[AuthController::class] = function() {
            return new AuthController(
                $this->get(LoginUseCase::class),
                $this->get(XssProtectionService::class)
            );
        };

        $this->factories[RegisterController::class] = function() {
            return new RegisterController(
                $this->get(UserRegisterUseCase::class),
                $this->get(XssProtectionService::class)
            );
        };

        $this->factories[RefreshTokenController::class] = function() {
            return new RefreshTokenController($this->get(JwtService::class));
        };

        $this->factories[OwnerController::class] = function() {
            return new OwnerController(
                $this->get(OwnerService::class),
                $this->get(XssProtectionService::class)
            );
        };

        // Contrôleur de santé
        $this->factories[\App\Interface\Controller\dbHealthController::class] = function() {
            return new \App\Interface\Controller\dbHealthController($this->get('db'));
        };
    }

    public function get(string $id): mixed
    {
        if (isset($this->services[$id])) {
            return $this->services[$id];
        }

        if (isset($this->factories[$id])) {
            $this->services[$id] = $this->factories[$id]();
            return $this->services[$id];
        }

        throw new \RuntimeException("Service not found: $id");
    }

    public function has(string $id): bool
    {
        return isset($this->factories[$id]) || isset($this->services[$id]);
    }
}


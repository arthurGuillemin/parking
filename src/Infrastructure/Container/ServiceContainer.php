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
use App\Interface\Controller\ParkingController;
use App\Domain\Service\OwnerService;
use App\Domain\Service\ParkingService;
use App\Application\UseCase\Owner\GetAvailableSpots\GetAvailableSpotsUseCase;
use App\Application\UseCase\Owner\AddOpeningHour\AddOpeningHourUseCase;
use App\Application\UseCase\Owner\DeleteOpeningHour\DeleteOpeningHourUseCase;
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
        $this->factories[LoginUseCase::class] = function () {
            return new LoginUseCase(
                $this->get(UserRepositoryInterface::class),
                $this->get(OwnerRepositoryInterface::class),
                $this->get(JwtService::class),
                $this->get(PasswordHasherInterface::class)
            );
        };

        $this->factories[UserRegisterUseCase::class] = function () {
            return new UserRegisterUseCase(
                $this->get(UserRepositoryInterface::class),
                $this->get(UserRegistrationValidator::class),
                $this->get(PasswordHasherInterface::class)
            );
        };

        // Services métier
        $this->factories[OwnerService::class] = function () {
            return new OwnerService(
                $this->get(OwnerRepositoryInterface::class),
                $this->get(PasswordHasherInterface::class),
                $this->get(JwtService::class)
            );
        };

        $this->factories[ParkingService::class] = function () {
            return new ParkingService(
                $this->get(ParkingRepositoryInterface::class)
            );
        };

        $this->factories[\App\Domain\Service\PricingRuleService::class] = function () {
            return new \App\Domain\Service\PricingRuleService(
                $this->get(PricingRuleRepositoryInterface::class)
            );
        };

        // Pricing & Hours UseCases
        $this->factories[AddOpeningHourUseCase::class] = function () {
            return new AddOpeningHourUseCase($this->get(OpeningHourRepositoryInterface::class));
        };
        $this->factories[DeleteOpeningHourUseCase::class] = function () {
            return new DeleteOpeningHourUseCase($this->get(OpeningHourRepositoryInterface::class));
        };

        $this->factories[\App\Domain\Service\OpeningHourService::class] = function () {
            return new \App\Domain\Service\OpeningHourService(
                $this->get(OpeningHourRepositoryInterface::class),
                $this->get(AddOpeningHourUseCase::class),
                $this->get(DeleteOpeningHourUseCase::class)
            );
        };

        // Controllers
        $this->factories[AuthController::class] = function () {
            return new AuthController(
                $this->get(LoginUseCase::class),
                $this->get(XssProtectionService::class)
            );
        };

        $this->factories[RegisterController::class] = function () {
            return new RegisterController(
                $this->get(UserRegisterUseCase::class),
                $this->get(XssProtectionService::class)
            );
        };

        $this->factories[RefreshTokenController::class] = function () {
            return new RefreshTokenController($this->get(JwtService::class));
        };

        $this->factories[OwnerController::class] = function () {
            return new OwnerController(
                $this->get(OwnerService::class),
                $this->get(XssProtectionService::class),
                $this->get(JwtService::class)
            );
        };

        $this->factories[\App\Interface\Controller\HomeController::class] = function () {
            return new \App\Interface\Controller\HomeController();
        };

        $this->factories[\App\Interface\Controller\ParkingController::class] = function () {
            return new \App\Interface\Controller\ParkingController(
                $this->get(ParkingService::class),
                $this->get(JwtService::class),
                $this->get(XssProtectionService::class)
            );
        };

        $this->factories[\App\Interface\Controller\PricingRuleController::class] = function () {
            return new \App\Interface\Controller\PricingRuleController(
                $this->get(\App\Domain\Service\PricingRuleService::class)
            );
        };

        $this->factories[\App\Interface\Controller\OpeningHourController::class] = function () {
            return new \App\Interface\Controller\OpeningHourController(
                $this->get(\App\Domain\Service\OpeningHourService::class)
            );
        };

        $this->factories[\App\Application\UseCase\User\CreateReservation\CreateReservationUseCase::class] = function () {
            return new \App\Application\UseCase\User\CreateReservation\CreateReservationUseCase(
                $this->get(ReservationRepositoryInterface::class),
                $this->get(\App\Domain\Service\ParkingAvailabilityService::class),
                $this->get(\App\Domain\Service\PricingService::class)
            );
        };

        $this->factories[\App\Domain\Service\CheckAvailabilityService::class] = function () {
            return new \App\Domain\Service\CheckAvailabilityService(
                $this->get(ReservationRepositoryInterface::class)
            );
        };

        $this->factories[\App\Application\UseCase\User\MakeReservation\MakeReservationUseCase::class] = function () {
            return new \App\Application\UseCase\User\MakeReservation\MakeReservationUseCase(
                $this->get(ReservationRepositoryInterface::class),
                $this->get(ParkingRepositoryInterface::class),
                $this->get(\App\Domain\Service\CheckAvailabilityService::class),
                $this->get(\App\Domain\Service\PricingService::class)
            );
        };

        $this->factories[\App\Domain\Service\PricingService::class] = function () {
            return new \App\Domain\Service\PricingService(
                $this->get(PricingRuleRepositoryInterface::class)
            );
        };

        $this->factories[\App\Domain\Service\ReservationService::class] = function () {
            return new \App\Domain\Service\ReservationService(
                $this->get(ReservationRepositoryInterface::class),
                $this->get(\App\Application\UseCase\User\CreateReservation\CreateReservationUseCase::class)
            );
        };

        $this->factories[\App\Interface\Controller\ReservationController::class] = function () {
            return new \App\Interface\Controller\ReservationController(
                $this->get(\App\Domain\Service\ReservationService::class),
                $this->get(\App\Application\UseCase\User\MakeReservation\MakeReservationUseCase::class),
                $this->get(\App\Domain\Service\ParkingService::class),
                $this->get(JwtService::class)
            );
        };

        $this->factories[\App\Application\UseCase\User\EnterParking\EnterParkingUseCase::class] = function () {
            return new \App\Application\UseCase\User\EnterParking\EnterParkingUseCase(
                $this->get(ParkingSessionRepositoryInterface::class),
                $this->get(ReservationRepositoryInterface::class),
                $this->get(SubscriptionRepositoryInterface::class)
            );
        };
        $this->factories[\App\Application\UseCase\User\ExitParking\ExitParkingUseCase::class] = function () {
            return new \App\Application\UseCase\User\ExitParking\ExitParkingUseCase(
                $this->get(ParkingSessionRepositoryInterface::class),
                $this->get(ReservationRepositoryInterface::class),
                $this->get(InvoiceRepositoryInterface::class),
                $this->get(\App\Domain\Service\PricingService::class)
            );
        };

        $this->factories[\App\Application\UseCase\Owner\UpdatePricingRule\UpdatePricingRuleUseCase::class] = function () {
            return new \App\Application\UseCase\Owner\UpdatePricingRule\UpdatePricingRuleUseCase(
                $this->get(\App\Domain\Repository\PricingRuleRepositoryInterface::class)
            );
        };

        $this->factories[\App\Application\UseCase\Parking\CountAvailableParkingSpots\CountAvailableParkingSpotsUseCase::class] = function () {
            return new \App\Application\UseCase\Parking\CountAvailableParkingSpots\CountAvailableParkingSpotsUseCase(
                $this->get(ParkingRepositoryInterface::class),
                $this->get(ReservationRepositoryInterface::class),
                $this->get(SubscriptionRepositoryInterface::class)
            );
        };

        $this->factories[\App\Domain\Service\ParkingSessionService::class] = function () {
            return new \App\Domain\Service\ParkingSessionService(
                $this->get(ParkingSessionRepositoryInterface::class)
            );
        };

        // List User Invoices
        $this->factories[\App\Application\UseCase\User\ListUserInvoices\ListUserInvoicesUseCase::class] = function () {
            return new \App\Application\UseCase\User\ListUserInvoices\ListUserInvoicesUseCase(
                $this->get(InvoiceRepositoryInterface::class)
            );
        };

        $this->factories[\App\Application\UseCase\User\GetInvoice\GetInvoiceUseCase::class] = function () {
            return new \App\Application\UseCase\User\GetInvoice\GetInvoiceUseCase(
                $this->get(InvoiceRepositoryInterface::class)
            );
        };

        $this->factories[\App\Interface\Controller\InvoiceController::class] = function () {
            return new \App\Interface\Controller\InvoiceController(
                $this->get(\App\Application\UseCase\User\GetInvoice\GetInvoiceUseCase::class),
                $this->get(JwtService::class),
                $this->get(UserRepositoryInterface::class)
            );
        };

        $this->factories[\App\Interface\Controller\ParkingSessionController::class] = function () {
            return new \App\Interface\Controller\ParkingSessionController(
                $this->get(\App\Domain\Service\ParkingSessionService::class),
                $this->get(\App\Application\UseCase\User\EnterParking\EnterParkingUseCase::class),
                $this->get(\App\Application\UseCase\User\ExitParking\ExitParkingUseCase::class),
                $this->get(\App\Application\UseCase\User\ListUserReservations\ListUserReservationsUseCase::class),
                $this->get(\App\Application\UseCase\User\ListUserSubscriptions\ListUserSubscriptionsUseCase::class),
                $this->get(JwtService::class),
                $this->get(ParkingSessionRepositoryInterface::class),
                $this->get(ReservationRepositoryInterface::class),
                $this->get(SubscriptionRepositoryInterface::class)
            );
        };

        // SubscriptionType Dependencies
        $this->factories[\App\Interface\Presenter\SubscriptionTypePresenter::class] = fn() => new \App\Interface\Presenter\SubscriptionTypePresenter();

        $this->factories[\App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeUseCase::class] = function () {
            return new \App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeUseCase(
                $this->get(SubscriptionTypeRepositoryInterface::class)
            );
        };
        $this->factories[\App\Application\UseCase\Owner\ListSubscriptionTypes\ListSubscriptionTypesUseCase::class] = function () {
            return new \App\Application\UseCase\Owner\ListSubscriptionTypes\ListSubscriptionTypesUseCase(
                $this->get(SubscriptionTypeRepositoryInterface::class)
            );
        };
        $this->factories[\App\Application\UseCase\Owner\GetSubscriptionType\GetSubscriptionTypeUseCase::class] = function () {
            return new \App\Application\UseCase\Owner\GetSubscriptionType\GetSubscriptionTypeUseCase(
                $this->get(SubscriptionTypeRepositoryInterface::class)
            );
        };

        // Subscription Slot UseCases
        $this->factories[\App\Application\UseCase\Owner\AddSubscriptionSlot\AddSubscriptionSlotUseCase::class] = function () {
            return new \App\Application\UseCase\Owner\AddSubscriptionSlot\AddSubscriptionSlotUseCase(
                $this->get(SubscriptionSlotRepositoryInterface::class)
            );
        };
        $this->factories[\App\Application\UseCase\Owner\ListSubscriptionSlots\ListSubscriptionSlotsUseCase::class] = function () {
            return new \App\Application\UseCase\Owner\ListSubscriptionSlots\ListSubscriptionSlotsUseCase(
                $this->get(SubscriptionSlotRepositoryInterface::class)
            );
        };
        $this->factories[\App\Application\UseCase\Owner\DeleteSubscriptionSlot\DeleteSubscriptionSlotUseCase::class] = function () {
            return new \App\Application\UseCase\Owner\DeleteSubscriptionSlot\DeleteSubscriptionSlotUseCase(
                $this->get(SubscriptionSlotRepositoryInterface::class)
            );
        };

        $this->factories[\App\Interface\Controller\SubscriptionTypeController::class] = function () {
            return new \App\Interface\Controller\SubscriptionTypeController(
                $this->get(\App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeUseCase::class),
                $this->get(\App\Application\UseCase\Owner\ListSubscriptionTypes\ListSubscriptionTypesUseCase::class),
                $this->get(\App\Application\UseCase\Owner\GetSubscriptionType\GetSubscriptionTypeUseCase::class),
                $this->get(\App\Interface\Presenter\SubscriptionTypePresenter::class)
            );
        };

        // User Subscriptions UseCases
        $this->factories[\App\Application\UseCase\User\AddSubscription\AddSubscriptionUseCase::class] = function () {
            return new \App\Application\UseCase\User\AddSubscription\AddSubscriptionUseCase(
                $this->get(SubscriptionRepositoryInterface::class)
            );
        };
        $this->factories[\App\Application\UseCase\User\ListUserSubscriptions\ListUserSubscriptionsUseCase::class] = function () {
            return new \App\Application\UseCase\User\ListUserSubscriptions\ListUserSubscriptionsUseCase(
                $this->get(SubscriptionRepositoryInterface::class),
                $this->get(ParkingRepositoryInterface::class)
            );
        };
        $this->factories[\App\Application\UseCase\User\GetSubscription\GetSubscriptionUseCase::class] = function () {
            return new \App\Application\UseCase\User\GetSubscription\GetSubscriptionUseCase(
                $this->get(SubscriptionRepositoryInterface::class)
            );
        };
        $this->factories[\App\Application\UseCase\User\CancelSubscription\CancelSubscriptionUseCase::class] = function () {
            return new \App\Application\UseCase\User\CancelSubscription\CancelSubscriptionUseCase(
                $this->get(SubscriptionRepositoryInterface::class)
            );
        };

        $this->factories[\App\Interface\Presenter\SubscriptionPresenter::class] = fn() => new \App\Interface\Presenter\SubscriptionPresenter();

        $this->factories[\App\Interface\Controller\SubscriptionController::class] = function () {
            return new \App\Interface\Controller\SubscriptionController(
                $this->get(\App\Application\UseCase\User\AddSubscription\AddSubscriptionUseCase::class),
                $this->get(\App\Application\UseCase\User\ListUserSubscriptions\ListUserSubscriptionsUseCase::class),
                $this->get(\App\Application\UseCase\User\GetSubscription\GetSubscriptionUseCase::class),
                $this->get(\App\Application\UseCase\User\CancelSubscription\CancelSubscriptionUseCase::class),
                $this->get(\App\Application\UseCase\Owner\ListSubscriptionTypes\ListSubscriptionTypesUseCase::class),
                $this->get(\App\Domain\Service\ParkingService::class),
                $this->get(JwtService::class),
                $this->get(\App\Interface\Presenter\SubscriptionPresenter::class)
            );
        };

        // User Dashboard & History
        $this->factories[\App\Application\UseCase\User\ListUserReservations\ListUserReservationsUseCase::class] = function () {
            return new \App\Application\UseCase\User\ListUserReservations\ListUserReservationsUseCase(
                $this->get(ReservationRepositoryInterface::class),
                $this->get(ParkingRepositoryInterface::class)
            );
        };
        $this->factories[\App\Application\UseCase\User\ListUserSessions\ListUserSessionsUseCase::class] = function () {
            return new \App\Application\UseCase\User\ListUserSessions\ListUserSessionsUseCase(
                $this->get(ParkingSessionRepositoryInterface::class),
                $this->get(ParkingRepositoryInterface::class)
            );
        };

        // User Controller
        $this->factories[\App\Interface\Controller\UserController::class] = function () {
            return new \App\Interface\Controller\UserController(
                $this->get(\App\Application\UseCase\User\ListUserReservations\ListUserReservationsUseCase::class),
                $this->get(\App\Application\UseCase\User\ListUserSessions\ListUserSessionsUseCase::class),
                $this->get(\App\Application\UseCase\User\ListUserSubscriptions\ListUserSubscriptionsUseCase::class),
                $this->get(\App\Application\UseCase\User\ListUserInvoices\ListUserInvoicesUseCase::class),
                $this->get(JwtService::class)
            );
        };

        // Alerts Controller & Dependencies
        $this->factories[\App\Domain\Repository\SubscriptionSlotRepositoryInterface::class] = function () {
            return new \App\Infrastructure\Persistence\Sql\SqlSubscriptionSlotRepository();
        };

        $this->factories[\App\Domain\Repository\InvoiceRepositoryInterface::class] = function () {
            return new \App\Infrastructure\Persistence\Sql\SqlInvoiceRepository();
        };

        $this->factories[\App\Domain\Service\SubscriptionCoverageService::class] = function () {
            return new \App\Domain\Service\SubscriptionCoverageService(
                $this->get(SubscriptionSlotRepositoryInterface::class)
            );
        };

        $this->factories[\App\Application\UseCase\Owner\ListSessionsOutOfReservationOrSubscription\ListSessionsOutOfReservationOrSubscriptionUseCase::class] = function () {
            return new \App\Application\UseCase\Owner\ListSessionsOutOfReservationOrSubscription\ListSessionsOutOfReservationOrSubscriptionUseCase(
                $this->get(ParkingSessionRepositoryInterface::class),
                $this->get(ReservationRepositoryInterface::class),
                $this->get(SubscriptionRepositoryInterface::class),
                $this->get(\App\Domain\Service\SubscriptionCoverageService::class)
            );
        };

        $this->factories[\App\Interface\Controller\SessionsOutOfReservationOrSubscriptionController::class] = function () {
            return new \App\Interface\Controller\SessionsOutOfReservationOrSubscriptionController(
                $this->get(\App\Application\UseCase\Owner\ListSessionsOutOfReservationOrSubscription\ListSessionsOutOfReservationOrSubscriptionUseCase::class)
            );
        };

        // Availability & Revenue Dependencies
        $this->factories[GetAvailableSpotsUseCase::class] = function () {
            return new GetAvailableSpotsUseCase(
                $this->get(ParkingRepositoryInterface::class),
                $this->get(ParkingSessionRepositoryInterface::class),
                $this->get(ReservationRepositoryInterface::class),
                $this->get(SubscriptionRepositoryInterface::class),
                $this->get(\App\Domain\Service\SubscriptionCoverageService::class)
            );
        };

        $this->factories[\App\Domain\Service\ParkingAvailabilityService::class] = function () {
            return new \App\Domain\Service\ParkingAvailabilityService(
                $this->get(GetAvailableSpotsUseCase::class),
                $this->get(OpeningHourRepositoryInterface::class)
            );
        };
        $this->factories[\App\Interface\Controller\ParkingAvailabilityController::class] = function () {
            return new \App\Interface\Controller\ParkingAvailabilityController(
                $this->get(\App\Domain\Service\ParkingAvailabilityService::class)
            );
        };

        $this->factories[\App\Domain\Service\MonthlyRevenueService::class] = function () {
            return new \App\Domain\Service\MonthlyRevenueService(
                $this->get(\App\Domain\Repository\InvoiceRepositoryInterface::class),
                $this->get(SubscriptionRepositoryInterface::class)
            );
        };
        $this->factories[\App\Interface\Controller\MonthlyRevenueController::class] = function () {
            return new \App\Interface\Controller\MonthlyRevenueController(
                $this->get(\App\Domain\Service\MonthlyRevenueService::class)
            );
        };

        // Contrôleur de santé
        $this->factories[\App\Interface\Controller\dbHealthController::class] = function () {
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

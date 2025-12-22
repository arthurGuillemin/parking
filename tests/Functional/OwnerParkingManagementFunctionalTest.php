<?php

namespace Tests\Functional;

use App\Infrastructure\Database\Database;
use App\Infrastructure\Persistence\Sql\SqlParkingRepository;
use App\Infrastructure\Persistence\Sql\SqlInvoiceRepository;
use App\Infrastructure\Persistence\Sql\SqlSubscriptionRepository;
use App\Infrastructure\Persistence\Sql\SqlReservationRepository;
use App\Application\UseCase\Owner\GetMonthlyRevenue\GetMonthlyRevenueUseCase;
use App\Application\UseCase\Owner\GetMonthlyRevenue\GetMonthlyRevenueRequest;
use App\Domain\Entity\Parking;
use App\Domain\Entity\Invoice;
use App\Domain\Entity\Subscription;
use App\Domain\Entity\Reservation;
use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Repository\InvoiceRepositoryInterface;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;

/**
 * Test fonctionnel propriétaire : Gestion parking + Revenus mensuels
 */
class OwnerParkingManagementFunctionalTest extends BaseFunctionalTest
{
    private ParkingRepositoryInterface $parkingRepository;
    private InvoiceRepositoryInterface $invoiceRepository;
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private GetMonthlyRevenueUseCase $getMonthlyRevenueUseCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parkingRepository = $this->container->get(ParkingRepositoryInterface::class);
        $this->invoiceRepository = $this->container->get(InvoiceRepositoryInterface::class);
        $this->subscriptionRepository = $this->container->get(SubscriptionRepositoryInterface::class);
        $this->reservationRepository = $this->container->get(ReservationRepositoryInterface::class);

        $this->getMonthlyRevenueUseCase = new GetMonthlyRevenueUseCase(
            $this->invoiceRepository,
            $this->subscriptionRepository
        );
    }

    public function testMonthlyRevenueWithInvoicesOnly(): void
    {
        $parking = new Parking(
            1,
            'owner-1',
            'Parking Revenus',
            '123 Rue Test',
            48.8566,
            2.3522,
            10,
            true
        );
        $parking = $this->parkingRepository->save($parking);

        $reservation1 = new Reservation(
            0,
            'user-1',
            $parking->getParkingId(),
            new \DateTimeImmutable('2025-01-15 10:00:00'),
            new \DateTimeImmutable('2025-01-15 12:00:00'),
            'completed',
            15.0,
            18.0
        );
        $res1 = $this->reservationRepository->save($reservation1);

        $reservation2 = new Reservation(
            0,
            'user-2',
            $parking->getParkingId(),
            new \DateTimeImmutable('2025-01-20 14:00:00'),
            new \DateTimeImmutable('2025-01-20 16:00:00'),
            'completed',
            25.0,
            30.0
        );
        $res2 = $this->reservationRepository->save($reservation2);

        $invoice1 = new Invoice(
            0,
            $res1->getReservationId(),
            null,
            new \DateTimeImmutable('2025-01-15 10:00:00'),
            15.0,
            18.0,
            null,
            'reservation'
        );
        $this->invoiceRepository->save($invoice1);

        $invoice2 = new Invoice(
            0,
            $res2->getReservationId(),
            null,
            new \DateTimeImmutable('2025-01-20 14:00:00'),
            25.0,
            30.0,
            null,
            'reservation'
        );
        $this->invoiceRepository->save($invoice2);

        $request = new GetMonthlyRevenueRequest($parking->getParkingId(), 2025, 1);
        $revenue = $this->getMonthlyRevenueUseCase->execute($request);

        $this->assertEquals(48.0, $revenue['total'], 'Les revenus doivent être la somme des factures TTC (18 + 30)');
    }

    public function testMonthlyRevenueWithSubscriptionsOnly(): void
    {
        $parking = new Parking(
            2,
            'owner-1',
            'Parking Abonnements',
            '456 Rue Test',
            48.8566,
            2.3522,
            10,
            true
        );
        $parking = $this->parkingRepository->save($parking);

        $subscription1 = new Subscription(
            0,
            'user-1',
            $parking->getParkingId(),
            1,
            new \DateTimeImmutable('2025-01-01'),
            new \DateTimeImmutable('2025-02-01'),
            'active',
            49.99
        );
        $this->subscriptionRepository->save($subscription1);

        $subscription2 = new Subscription(
            0,
            'user-2',
            $parking->getParkingId(),
            1,
            new \DateTimeImmutable('2025-01-01'),
            new \DateTimeImmutable('2025-02-01'),
            'active',
            79.99
        );
        $this->subscriptionRepository->save($subscription2);

        $request = new GetMonthlyRevenueRequest($parking->getParkingId(), 2025, 1);
        $revenue = $this->getMonthlyRevenueUseCase->execute($request);

<<<<<<< HEAD
        $this->assertEquals(129.98, $revenue['total'], 'Les revenus doivent être la somme des prix mensuels (49.99 + 79.99)');
=======
        $this->assertEquals(129.98, $revenue['total'], 'Les revenus doivent être la somme des prix mensuels');
>>>>>>> main
    }

    public function testMonthlyRevenueWithInvoicesAndSubscriptions(): void
    {
        $parking = new Parking(
            3,
            'owner-1',
            'Parking Complet',
            '789 Rue Test',
            48.8566,
            2.3522,
            10,
            true
        );
        $parking = $this->parkingRepository->save($parking);

        $reservation = new Reservation(
            0,
            'user-3',
            $parking->getParkingId(),
            new \DateTimeImmutable('2025-01-10 10:00:00'),
            new \DateTimeImmutable('2025-01-10 12:00:00'),
            'completed',
            20.0,
            24.0
        );
        $res = $this->reservationRepository->save($reservation);

        $invoice = new Invoice(
            0,
            $res->getReservationId(),
            null,
            new \DateTimeImmutable('2025-01-10 10:00:00'),
            20.0,
            24.0,
            null,
            'reservation'
        );
        $this->invoiceRepository->save($invoice);

        $subscription = new Subscription(
            0,
            'user-3',
            $parking->getParkingId(),
            1,
            new \DateTimeImmutable('2025-01-01'),
            new \DateTimeImmutable('2025-02-01'),
            'active',
            59.99
        );
        $this->subscriptionRepository->save($subscription);

        $request = new GetMonthlyRevenueRequest($parking->getParkingId(), 2025, 1);
        $revenue = $this->getMonthlyRevenueUseCase->execute($request);

<<<<<<< HEAD
        $this->assertEqualsWithDelta(83.99, $revenue['total'], 0.01, 'Les revenus doivent être la somme des factures TTC + prix mensuel (24 + 59.99)');
=======
        $this->assertEqualsWithDelta(83.99, $revenue['total'], 0.01);
>>>>>>> main
    }

    public function testMonthlyRevenueExcludesOtherMonths(): void
    {
        $parking = new Parking(
            4,
            'owner-1',
            'Parking Filtre',
            '999 Rue Test',
            48.8566,
            2.3522,
            10,
            true
        );
        $parking = $this->parkingRepository->save($parking);

        $res1 = $this->reservationRepository->save(new Reservation(
            0,
            'user-4',
            $parking->getParkingId(),
            new \DateTimeImmutable('2025-01-15 10:00:00'),
            new \DateTimeImmutable('2025-01-15 12:00:00'),
            'completed',
            10.0,
            12.0
        ));

        $res2 = $this->reservationRepository->save(new Reservation(
            0,
            'user-5',
            $parking->getParkingId(),
            new \DateTimeImmutable('2025-02-15 10:00:00'),
            new \DateTimeImmutable('2025-02-15 12:00:00'),
            'completed',
            20.0,
            24.0
        ));

        $this->invoiceRepository->save(new Invoice(
            0,
            $res1->getReservationId(),
            null,
            new \DateTimeImmutable('2025-01-15 10:00:00'),
            10.0,
            12.0,
            null,
            'reservation'
        ));

        $this->invoiceRepository->save(new Invoice(
            0,
            $res2->getReservationId(),
            null,
            new \DateTimeImmutable('2025-02-15 10:00:00'),
            20.0,
            24.0,
            null,
            'reservation'
        ));

        $request = new GetMonthlyRevenueRequest($parking->getParkingId(), 2025, 1);
        $revenue = $this->getMonthlyRevenueUseCase->execute($request);

<<<<<<< HEAD
        $this->assertEquals(12.0, $revenue['total'], 'Seules les factures de janvier doivent être comptabilisées');
=======
        $this->assertEquals(12.0, $revenue['total']);
>>>>>>> main
    }
}

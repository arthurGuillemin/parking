<?php

namespace Tests\Functional;

use App\Infrastructure\Database\Database;
use App\Infrastructure\Persistence\Sql\SqlParkingRepository;
use App\Infrastructure\Persistence\Sql\SqlReservationRepository;
use App\Infrastructure\Persistence\Sql\SqlParkingSessionRepository;
use App\Application\UseCase\Owner\ListReservations\ListReservationsUseCase;
use App\Application\UseCase\Owner\ListReservations\ListReservationsRequest;
use App\Application\UseCase\Owner\ListParkingSessions\ListParkingSessionsUseCase;
use App\Application\UseCase\Owner\ListParkingSessions\ListParkingSessionsRequest;
use App\Domain\Entity\Parking;
use App\Domain\Entity\Reservation;
use App\Domain\Entity\ParkingSession;
use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Repository\ParkingSessionRepositoryInterface;

/**
 * Test fonctionnel propriétaire : Réservations + Stationnements
 */
class OwnerReservationsAndSessionsFunctionalTest extends BaseFunctionalTest
{
    private ParkingRepositoryInterface $parkingRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private ParkingSessionRepositoryInterface $parkingSessionRepository;
    private ListReservationsUseCase $listReservationsUseCase;
    private ListParkingSessionsUseCase $listParkingSessionsUseCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parkingRepository = $this->container->get(ParkingRepositoryInterface::class);
        $this->reservationRepository = $this->container->get(ReservationRepositoryInterface::class);
        $this->parkingSessionRepository = $this->container->get(ParkingSessionRepositoryInterface::class);

        $this->listReservationsUseCase = new ListReservationsUseCase($this->reservationRepository);
        $this->listParkingSessionsUseCase = new ListParkingSessionsUseCase($this->parkingSessionRepository);
    }

    public function testListAllReservationsForParking(): void
    {
        $parking = new Parking(
            1,
            'owner-1',
            'Parking Test',
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
            'active',
            15.0,
            null
        );
        $this->reservationRepository->save($reservation1);

        $reservation2 = new Reservation(
            0,
            'user-2',
            $parking->getParkingId(),
            new \DateTimeImmutable('2025-01-16 14:00:00'),
            new \DateTimeImmutable('2025-01-16 16:00:00'),
            'completed',
            20.0,
            20.0
        );
        $this->reservationRepository->save($reservation2);

        $request = new ListReservationsRequest($parking->getParkingId());
        $reservations = $this->listReservationsUseCase->execute($request);

        $this->assertCount(2, $reservations, 'Le parking doit avoir 2 réservations');
        foreach ($reservations as $res) {
            $this->assertEquals($parking->getParkingId(), $res->getParkingId());
        }
    }

    public function testFilterReservationsByDateRange(): void
    {
        $parking = new Parking(
            2,
            'owner-1',
            'Parking Filtre',
            '456 Rue Test',
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
            'active',
            15.0,
            null
        );
        $this->reservationRepository->save($reservation1);

        $reservation2 = new Reservation(
            0,
            'user-2',
            $parking->getParkingId(),
            new \DateTimeImmutable('2025-02-15 14:00:00'),
            new \DateTimeImmutable('2025-02-15 16:00:00'),
            'active',
            20.0,
            null
        );
        $this->reservationRepository->save($reservation2);

        $start = new \DateTimeImmutable('2025-01-01 00:00:00');
        $end = new \DateTimeImmutable('2025-01-31 23:59:59');
        $request = new ListReservationsRequest($parking->getParkingId(), $start, $end);
        $reservations = $this->listReservationsUseCase->execute($request);

        $this->assertCount(1, $reservations, 'Seule la réservation de janvier doit être retournée');
        $this->assertEquals('user-1', $reservations[0]->getUserId());
    }

    public function testListAllParkingSessionsForParking(): void
    {
        $parking = new Parking(
            3,
            'owner-1',
            'Parking Sessions',
            '789 Rue Test',
            48.8566,
            2.3522,
            10,
            true
        );
        $parking = $this->parkingRepository->save($parking);

        $session1 = new ParkingSession(
            0,
            'user-1',
            $parking->getParkingId(),
            1,
            new \DateTimeImmutable('2025-01-15 10:00:00'),
            new \DateTimeImmutable('2025-01-15 12:00:00'),
            15.0,
            false
        );
        $this->parkingSessionRepository->save($session1);

        $session2 = new ParkingSession(
            0,
            'user-2',
            $parking->getParkingId(),
            null,
            new \DateTimeImmutable('2025-01-16 14:00:00'),
            null,
            null,
            false
        );
        $this->parkingSessionRepository->save($session2);

        $request = new ListParkingSessionsRequest($parking->getParkingId());
        $sessions = $this->listParkingSessionsUseCase->execute($request);

        $this->assertCount(2, $sessions, 'Le parking doit avoir 2 stationnements');
        foreach ($sessions as $session) {
            $this->assertEquals($parking->getParkingId(), $session->getParkingId());
        }
    }

    public function testParkingSessionsAreFilteredByParkingId(): void
    {
        $parking1 = new Parking(
            4,
            'owner-1',
            'Parking 1',
            '111 Rue Test',
            48.8566,
            2.3522,
            10,
            true
        );
        $parking1 = $this->parkingRepository->save($parking1);

        $parking2 = new Parking(
            5,
            'owner-1',
            'Parking 2',
            '222 Rue Test',
            48.8566,
            2.3522,
            10,
            true
        );
        $parking2 = $this->parkingRepository->save($parking2);

        $session1 = new ParkingSession(
            0,
            'user-1',
            $parking1->getParkingId(),
            1,
            new \DateTimeImmutable('2025-01-15 10:00:00'),
            new \DateTimeImmutable('2025-01-15 12:00:00'),
            15.0,
            false
        );
        $this->parkingSessionRepository->save($session1);

        $session2 = new ParkingSession(
            0,
            'user-2',
            $parking2->getParkingId(),
            null,
            new \DateTimeImmutable('2025-01-16 14:00:00'),
            null,
            null,
            false
        );
        $this->parkingSessionRepository->save($session2);

        $request = new ListParkingSessionsRequest($parking1->getParkingId());
        $sessions = $this->listParkingSessionsUseCase->execute($request);

        $this->assertCount(1, $sessions, 'Seul le stationnement du parking 1 doit être retourné');
        $this->assertEquals($parking1->getParkingId(), $sessions[0]->getParkingId());
    }
}

<?php

namespace Tests\Functional;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\Database\Database;
use App\Infrastructure\Persistence\SubscriptionTypeRepository;
use App\Infrastructure\Persistence\SubscriptionSlotRepository;
use App\Infrastructure\Persistence\SubscriptionRepository;
use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeUseCase;
use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeRequest;
use App\Application\UseCase\Owner\AddSubscriptionSlot\AddSubscriptionSlotUseCase;
use App\Application\UseCase\Owner\AddSubscriptionSlot\AddSubscriptionSlotRequest;
use App\Application\UseCase\User\AddSubscription\AddSubscriptionUseCase;
use App\Application\UseCase\User\AddSubscription\AddSubscriptionRequest;
use App\Application\UseCase\User\ListUserSubscriptions\ListUserSubscriptionsUseCase;
use App\Application\UseCase\User\ListUserSubscriptions\ListUserSubscriptionsRequest;
use App\Application\UseCase\User\GetSubscription\GetSubscriptionUseCase;
use App\Application\UseCase\User\GetSubscription\GetSubscriptionRequest;
use App\Application\UseCase\User\CancelSubscription\CancelSubscriptionUseCase;
use App\Application\UseCase\User\CancelSubscription\CancelSubscriptionRequest;
use App\Application\UseCase\Owner\ListSubscriptionSlots\ListSubscriptionSlotsUseCase;
use App\Application\UseCase\Owner\ListSubscriptionSlots\ListSubscriptionSlotsRequest;
use App\Application\UseCase\Owner\DeleteSubscriptionSlot\DeleteSubscriptionSlotUseCase;
use App\Application\UseCase\Owner\DeleteSubscriptionSlot\DeleteSubscriptionSlotRequest;
use App\Application\UseCase\Owner\ListSubscriptionTypes\ListSubscriptionTypesUseCase;
use App\Application\UseCase\Owner\ListSubscriptionTypes\ListSubscriptionTypesRequest;
use App\Application\UseCase\Owner\GetSubscriptionType\GetSubscriptionTypeUseCase;
use App\Application\UseCase\Owner\GetSubscriptionType\GetSubscriptionTypeRequest;
use App\Domain\Service\SubscriptionCoverageService;
use PDO;

class SubscriptionFunctionalTest extends BaseFunctionalTest
{
    private AddSubscriptionTypeUseCase $addTypeUseCase;
    private AddSubscriptionSlotUseCase $addSlotUseCase;
    private AddSubscriptionUseCase $addSubscriptionUseCase;
    private ListUserSubscriptionsUseCase $listSubscriptionsUseCase;
    private GetSubscriptionUseCase $getSubscriptionUseCase;
    private CancelSubscriptionUseCase $cancelSubscriptionUseCase;
    private ListSubscriptionSlotsUseCase $listSlotsUseCase;
    private DeleteSubscriptionSlotUseCase $deleteSlotUseCase;
    private ListSubscriptionTypesUseCase $listTypesUseCase;
    private GetSubscriptionTypeUseCase $getSubscriptionTypeUseCase;
    private SubscriptionCoverageService $coverageService;
    private \App\Domain\Repository\SubscriptionRepositoryInterface $subscriptionRepository;
    // We keep repo property access for specific assertions if needed, fetching from container

    protected function setUp(): void
    {
        parent::setUp(); // Sets up DB and Container

        $this->addTypeUseCase = $this->container->get(AddSubscriptionTypeUseCase::class);
        $this->addSlotUseCase = $this->container->get(AddSubscriptionSlotUseCase::class);
        $this->addSubscriptionUseCase = $this->container->get(AddSubscriptionUseCase::class);
        $this->listSubscriptionsUseCase = $this->container->get(ListUserSubscriptionsUseCase::class);
        $this->getSubscriptionUseCase = $this->container->get(GetSubscriptionUseCase::class);
        $this->cancelSubscriptionUseCase = $this->container->get(CancelSubscriptionUseCase::class);
        // listSlotsUseCase was newly added to container
        $this->listSlotsUseCase = $this->container->get(ListSubscriptionSlotsUseCase::class);
        $this->deleteSlotUseCase = $this->container->get(DeleteSubscriptionSlotUseCase::class);
        $this->listTypesUseCase = $this->container->get(ListSubscriptionTypesUseCase::class);
        $this->getSubscriptionTypeUseCase = $this->container->get(GetSubscriptionTypeUseCase::class);
        $this->coverageService = $this->container->get(SubscriptionCoverageService::class);

        $this->subscriptionRepository = $this->container->get(\App\Domain\Repository\SubscriptionRepositoryInterface::class);
    }

    /**
     * ✅ Scénario 1 : Créer un type d'abonnement "Week-end"
     */
    public function testCreateWeekendSubscriptionType(): void
    {
        $request = new AddSubscriptionTypeRequest(
            1, // parkingId
            'Weekend Access',
            'Vendredi 18h au Lundi 10h'
        );

        $type = $this->addTypeUseCase->execute($request);

        $this->assertIsInt($type->id);
        $this->assertEquals(1, $type->parkingId);
        $this->assertEquals('Weekend Access', $type->name);
        $this->assertEquals('Vendredi 18h au Lundi 10h', $type->description);
    }

    /**
     * ✅ Scénario 2 : Ajouter des créneaux horaires au type d'abonnement
     * Week-end = Vendredi 18h - Lundi 10h
     */
    public function testAddWeekendSlots(): void
    {
        // Créer le type
        $typeRequest = new AddSubscriptionTypeRequest(1, 'Weekend Access', 'Week-end');
        $type = $this->addTypeUseCase->execute($typeRequest);

        // Ajouter les créneaux : Vendredi 18h - 23:59
        $fridayRequest = new AddSubscriptionSlotRequest(
            $type->id,
            5, // Vendredi
            new \DateTimeImmutable('18:00:00'),
            new \DateTimeImmutable('23:59:59')
        );
        $fridaySlot = $this->addSlotUseCase->execute($fridayRequest);

        // Ajouter les créneaux : Samedi 00:00 - 23:59
        $saturdayRequest = new AddSubscriptionSlotRequest(
            $type->id,
            6, // Samedi
            new \DateTimeImmutable('00:00:00'),
            new \DateTimeImmutable('23:59:59')
        );
        $saturdaySlot = $this->addSlotUseCase->execute($saturdayRequest);

        // Ajouter les créneaux : Dimanche 00:00 - 23:59
        $sundayRequest = new AddSubscriptionSlotRequest(
            $type->id,
            7, // Dimanche
            new \DateTimeImmutable('00:00:00'),
            new \DateTimeImmutable('23:59:59')
        );
        $sundaySlot = $this->addSlotUseCase->execute($sundayRequest);

        // Ajouter les créneaux : Lundi 00:00 - 10:00
        $mondayRequest = new AddSubscriptionSlotRequest(
            $type->id,
            1, // Lundi
            new \DateTimeImmutable('00:00:00'),
            new \DateTimeImmutable('10:00:00')
        );
        $mondaySlot = $this->addSlotUseCase->execute($mondayRequest);

        // ✅ Vérifier que tous les créneaux ont été créés
        $this->assertIsInt($fridaySlot->id);
        $this->assertIsInt($saturdaySlot->id);
        $this->assertIsInt($sundaySlot->id);
        $this->assertIsInt($mondaySlot->id);
    }

    /**
     * ✅ Scénario 3 : Un utilisateur s'abonne au type "Week-end"
     */
    public function testUserSubscribesToWeekend(): void
    {
        // Créer le type et ajouter les créneaux
        $typeRequest = new AddSubscriptionTypeRequest(1, 'Weekend Access', 'Week-end');
        $type = $this->addTypeUseCase->execute($typeRequest);

        $slotRequest = new AddSubscriptionSlotRequest(
            $type->id,
            5,
            new \DateTimeImmutable('18:00:00'),
            new \DateTimeImmutable('23:59:59')
        );
        $this->addSlotUseCase->execute($slotRequest);

        // Utilisateur s'abonne
        $subscriptionRequest = new AddSubscriptionRequest(
            'user-john-doe', // userId
            1, // parkingId
            $type->id, // typeId
            new \DateTimeImmutable('2025-01-01'), // startDate
            new \DateTimeImmutable('2025-02-01'), // endDate
            49.99 // monthlyPrice
        );

        $subscription = $this->addSubscriptionUseCase->execute($subscriptionRequest);

        // ✅ Vérifier que l'abonnement a été créé
        $this->assertIsInt($subscription->id);
        $this->assertEquals('user-john-doe', $subscription->userId);
        $this->assertEquals(1, $subscription->parkingId);
        $this->assertEquals('active', $subscription->status);
        $this->assertEquals(49.99, $subscription->monthlyPrice);
    }

    /**
     * ✅ Scénario 4 : Vérifier que le service de couverture fonctionne correctement
     * L'utilisateur est couvert vendredi 20h, mais pas mardi 20h
     */
    public function testSubscriptionCoverageService(): void
    {
        // Créer le type et ajouter les créneaux
        $typeRequest = new AddSubscriptionTypeRequest(1, 'Weekend Access', 'Week-end');
        $type = $this->addTypeUseCase->execute($typeRequest);

        // Ajouter créneau vendredi
        $slotRequest = new AddSubscriptionSlotRequest(
            $type->id,
            5,
            new \DateTimeImmutable('18:00:00'),
            new \DateTimeImmutable('23:59:59')
        );
        $this->addSlotUseCase->execute($slotRequest);

        // Créer l'abonnement
        $subscriptionRequest = new AddSubscriptionRequest(
            'user-jane-doe',
            1,
            $type->id,
            new \DateTimeImmutable('2025-01-01'),
            new \DateTimeImmutable('2025-02-01'),
            49.99
        );
        $subscriptionResponse = $this->addSubscriptionUseCase->execute($subscriptionRequest);
        $subscription = $this->subscriptionRepository->findById($subscriptionResponse->id);

        // ✅ Test 1 : Vendredi 20h (couvert)
        $fridayEveningDateTime = new \DateTimeImmutable('2025-01-10 20:00:00'); // 2025-01-10 is Friday
        $this->assertTrue(
            $this->coverageService->isDateTimeCovered($subscription, $fridayEveningDateTime),
            'User should be covered on Friday 20h'
        );

        // ✅ Test 2 : Mardi 20h (non couvert)
        $tuesdayEveningDateTime = new \DateTimeImmutable('2025-01-07 20:00:00'); // 2025-01-07 is Tuesday
        $this->assertFalse(
            $this->coverageService->isDateTimeCovered($subscription, $tuesdayEveningDateTime),
            'User should NOT be covered on Tuesday 20h'
        );

        // ✅ Test 3 : Après la date de fin (non couvert)
        $afterEndDateTime = new \DateTimeImmutable('2025-02-15 20:00:00');
        $this->assertFalse(
            $this->coverageService->isDateTimeCovered($subscription, $afterEndDateTime),
            'User should NOT be covered after subscription end date'
        );
    }

    /**
     * ✅ Scénario 5 : Créer un abonnement 24/7 (typeId = null)
     */
    public function testTwentyFourSevenSubscription(): void
    {
        $subscriptionRequest = new AddSubscriptionRequest(
            'user-vip',
            1,
            null, // typeId = null = 24/7
            new \DateTimeImmutable('2025-01-01'),
            new \DateTimeImmutable('2025-02-01'),
            99.99
        );

        $subscriptionResponse = $this->addSubscriptionUseCase->execute($subscriptionRequest);
        $subscription = $this->subscriptionRepository->findById($subscriptionResponse->id);

        $this->assertNull($subscriptionResponse->typeId);
        $this->assertEquals('active', $subscriptionResponse->status);

        // ✅ Vérifier que le service accepte n'importe quelle heure
        $randomDateTime = new \DateTimeImmutable('2025-01-15 03:47:00');
        $this->assertTrue(
            $this->coverageService->isDateTimeCovered($subscription, $randomDateTime),
            'VIP user should be covered 24/7'
        );
    }

    /**
     * ✅ Scénario 6 : Lister les abonnements d'un utilisateur
     */
    public function testListUserSubscriptions(): void
    {
        // Créer un type
        $typeRequest = new AddSubscriptionTypeRequest(1, 'Weekend Access', 'Week-end');
        $type = $this->addTypeUseCase->execute($typeRequest);

        // Créer 2 abonnements pour le même utilisateur
        $sub1Request = new AddSubscriptionRequest(
            'user-alice',
            1,
            $type->id,
            new \DateTimeImmutable('2025-01-01'),
            new \DateTimeImmutable('2025-02-01'),
            49.99
        );
        $this->addSubscriptionUseCase->execute($sub1Request);

        $sub2Request = new AddSubscriptionRequest(
            'user-alice',
            2,
            null,
            new \DateTimeImmutable('2025-01-15'),
            new \DateTimeImmutable('2025-02-15'),
            99.99
        );
        $this->addSubscriptionUseCase->execute($sub2Request);

        // ✅ Lister les abonnements de l'utilisateur
        $listRequest = new ListUserSubscriptionsRequest('user-alice');
        $subscriptions = $this->listSubscriptionsUseCase->execute($listRequest);

        $this->assertCount(2, $subscriptions);
        $this->assertEquals('user-alice', $subscriptions[0]->userId);
        $this->assertEquals(2, $subscriptions[0]->parkingId);
        $this->assertEquals(1, $subscriptions[1]->parkingId);
    }

    /**
     * ✅ Scénario 7 : Validation de la durée minimale (1 mois)
     */
    public function testSubscriptionMinimumDuration(): void
    {
        $startDate = new \DateTimeImmutable('2025-01-01');
        $endDate = new \DateTimeImmutable('2025-01-15'); // Seulement 14 jours

        $subscriptionRequest = new AddSubscriptionRequest(
            'user-bob',
            1,
            null,
            $startDate,
            $endDate,
            49.99
        );

        // ✅ Doit lever une exception
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Subscription duration must be at least 1 month.');

        $this->addSubscriptionUseCase->execute($subscriptionRequest);
    }

    /**
     * ✅ Scénario 8 : Validation de la durée maximale (1 an)
     */
    public function testSubscriptionMaximumDuration(): void
    {
        $startDate = new \DateTimeImmutable('2025-01-01');
        $endDate = new \DateTimeImmutable('2026-01-15'); // 1 an + 14 jours

        $subscriptionRequest = new AddSubscriptionRequest(
            'user-charlie',
            1,
            null,
            $startDate,
            $endDate,
            49.99
        );

        // ✅ Doit lever une exception
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Subscription duration cannot exceed 1 year.');

        $this->addSubscriptionUseCase->execute($subscriptionRequest);
    }

    /**
     * ✅ Scénario 9 : Validation des créneaux (jour entre 1 et 7)
     */
    public function testSlotWeekdayValidation(): void
    {
        $typeRequest = new AddSubscriptionTypeRequest(1, 'Test Type', null);
        $type = $this->addTypeUseCase->execute($typeRequest);

        $invalidSlotRequest = new AddSubscriptionSlotRequest(
            $type->id,
            8, // Invalid: doit être 1-7
            new \DateTimeImmutable('18:00:00'),
            new \DateTimeImmutable('22:00:00')
        );

        // ✅ Doit lever une exception
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Weekday must be between 1 (Monday) and 7 (Sunday).');

        $this->addSlotUseCase->execute($invalidSlotRequest);
    }

    /**
     * ✅ Scénario 10 : Validation des créneaux (l'heure de début < heure de fin)
     */
    public function testSlotTimeValidation(): void
    {
        $typeRequest = new AddSubscriptionTypeRequest(1, 'Test Type', null);
        $type = $this->addTypeUseCase->execute($typeRequest);

        $invalidSlotRequest = new AddSubscriptionSlotRequest(
            $type->id,
            3,
            new \DateTimeImmutable('22:00:00'),
            new \DateTimeImmutable('18:00:00') // Start après end = INVALID
        );

        // ✅ Doit lever une exception
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Start time must be before end time.');

        $this->addSlotUseCase->execute($invalidSlotRequest);
    }

    /**
     * ✅ Scénario 11 : Récupérer un abonnement par ID
     */
    public function testGetSubscriptionById(): void
    {
        $startDate = new \DateTimeImmutable('2025-01-01');
        $endDate = new \DateTimeImmutable('2025-02-01');

        $subscriptionRequest = new AddSubscriptionRequest(
            'user-david',
            1,
            null,
            $startDate,
            $endDate,
            49.99
        );
        $created = $this->addSubscriptionUseCase->execute($subscriptionRequest);

        $request = new GetSubscriptionRequest($created->id);
        $retrieved = $this->getSubscriptionUseCase->execute($request);

        $this->assertEquals($created->id, $retrieved->id);
        $this->assertEquals('user-david', $retrieved->userId);
        $this->assertEquals('active', $retrieved->status);
    }

    /**
     * ✅ Scénario 12 : Annuler un abonnement
     */
    public function testCancelSubscription(): void
    {
        $startDate = new \DateTimeImmutable('2025-01-01');
        $endDate = new \DateTimeImmutable('2025-02-01');

        $subscriptionRequest = new AddSubscriptionRequest(
            'user-eve',
            1,
            null,
            $startDate,
            $endDate,
            49.99
        );
        $created = $this->addSubscriptionUseCase->execute($subscriptionRequest);

        $request = new CancelSubscriptionRequest($created->id);
        $cancelled = $this->cancelSubscriptionUseCase->execute($request);

        $this->assertEquals($created->id, $cancelled->id);
        $this->assertEquals('cancelled', $cancelled->status);

        // Verify persistence
        $getRequest = new GetSubscriptionRequest($created->id);
        $retrieved = $this->getSubscriptionUseCase->execute($getRequest);
        $this->assertEquals('cancelled', $retrieved->status);
    }

    /**
     * ✅ Scénario 13 : Lister les créneaux par type
     */
    public function testListSlotsByType(): void
    {
        $typeRequest = new AddSubscriptionTypeRequest(1, 'Test Slots', null);
        $type = $this->addTypeUseCase->execute($typeRequest);

        $this->addSlotUseCase->execute(new AddSubscriptionSlotRequest($type->id, 1, new \DateTimeImmutable('09:00'), new \DateTimeImmutable('10:00')));
        $this->addSlotUseCase->execute(new AddSubscriptionSlotRequest($type->id, 2, new \DateTimeImmutable('09:00'), new \DateTimeImmutable('10:00')));

        $listRequest = new ListSubscriptionSlotsRequest($type->id);
        $slots = $this->listSlotsUseCase->execute($listRequest);

        $this->assertCount(2, $slots);
        $this->assertEquals(1, $slots[0]->weekday);
        $this->assertEquals(2, $slots[1]->weekday);
    }

    /**
     * ✅ Scénario 14 : Supprimer un créneau
     */
    public function testDeleteSlot(): void
    {
        $typeRequest = new AddSubscriptionTypeRequest(1, 'Test Delete', null);
        $type = $this->addTypeUseCase->execute($typeRequest);

        $slot = $this->addSlotUseCase->execute(new AddSubscriptionSlotRequest($type->id, 1, new \DateTimeImmutable('09:00'), new \DateTimeImmutable('10:00')));

        $this->deleteSlotUseCase->execute(new DeleteSubscriptionSlotRequest($slot->id));

        $listRequest = new ListSubscriptionSlotsRequest($type->id);
        $slots = $this->listSlotsUseCase->execute($listRequest);

        $this->assertCount(0, $slots);
    }

    /**
     * ✅ Scénario 15 : Récupérer un type d'abonnement
     */
    public function testGetSubscriptionType(): void
    {
        $request = new AddSubscriptionTypeRequest(1, 'Type Get', 'Desc');
        $created = $this->addTypeUseCase->execute($request);

        $getRequest = new GetSubscriptionTypeRequest($created->id);
        $type = $this->getSubscriptionTypeUseCase->execute($getRequest);

        $this->assertEquals($created->id, $type->id);
        $this->assertEquals('Type Get', $type->name);
    }

    /**
     * ✅ Scénario 16 : Lister les types d'abonnement
     */
    public function testListSubscriptionTypes(): void
    {
        // On s'assure d'avoir au moins 2 types (celui du scénario précédent + un nouveau)
        // Mais en test global, les données persistent dans la DB mémoire, donc on peut compter.
        // On va juste créer 2 nouveaux et vérifier qu'on les trouve.

        $request1 = new AddSubscriptionTypeRequest(10, 'Type List 1', 'Desc');
        $this->addTypeUseCase->execute($request1);

        $request2 = new AddSubscriptionTypeRequest(10, 'Type List 2', 'Desc');
        $this->addTypeUseCase->execute($request2);

        $listRequest = new ListSubscriptionTypesRequest(10);
        $types = $this->listTypesUseCase->execute($listRequest);

        // findByAll retourne TOUT, donc on vérifie qu'on a au moins ces 2 là.
        $this->assertGreaterThanOrEqual(2, count($types));

        // Vérifions qu'un des types est bien celui qu'on a créé
        $found = false;
        foreach ($types as $t) {
            if ($t->name === 'Type List 1') {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Type List 1 should be in the list');
    }
}

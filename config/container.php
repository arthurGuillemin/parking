<?php
// Configuration minimale du conteneur (retourne un tableau de services)
use App\Domain\Repository\UserRepositoryInterface;
use App\Infrastructure\Database\Database;
use App\Infrastructure\Persistence\Sql\SqlOwnerRepository;
use App\Infrastructure\Persistence\Sql\SqlUserRepository;

return [
    'db' => Database::getInstance(),

    UserRepositoryInterface::class => function(\Psr\Container\ContainerInterface $c): UserRepositoryInterface {
        return new SqlUserRepository($c->get('db'));
    },

];

<?php
// Configuration minimale du conteneur (retourne un tableau de services)
use App\Domain\Repository\UserRepositoryInterface;
use App\Infrastructure\Database\Database;
use App\Infrastructure\Persistence\Sql\SqlOwnerRepository;
use App\Infrastructure\Persistence\Sql\SqlUserRepository;
use App\Infrastructure\Persistence\File\FileUserRepository;

return [
    'db' => Database::getInstance(),

    UserRepositoryInterface::class => function(\Psr\Container\ContainerInterface $c): UserRepositoryInterface {
        $driver = $_ENV['STORAGE_DRIVER'] ?? 'sql';

        return match ($driver) {
            'file' => new FileUserRepository(),
            'sql'  => new SqlUserRepository($c->get('db')),
            default => throw new RuntimeException("Invalid STORAGE_DRIVER"),
        };
    },

];

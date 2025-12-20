<?php

namespace App\Infrastructure\Persistence\File;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Infrastructure\Storage\FileStorage;
use DateTimeImmutable;

class FileUserRepository implements UserRepositoryInterface
{
    private FileStorage $storage;

    public function __construct()
    {
        $this->storage = new FileStorage(__DIR__ . '/storage/users.json');
    }

    public function findById(string $id): ?User
    {
        foreach ($this->storage->read() as $row) {
            if ($row['id'] === $id) {
                return $this->mapToUser($row);
            }
        }
        return null;
    }

    public function findByEmail(string $email): ?User
    {
        foreach ($this->storage->read() as $row) {
            if ($row['email'] === $email) {
                return $this->mapToUser($row);
            }
        }
        return null;
    }

    public function save(User $user): User
    {
        $data = $this->storage->read();
        $found = false;

        foreach ($data as &$row) {
            if ($row['id'] === $user->getUserId()) {
                $row = $this->mapFromUser($user);
                $found = true;
                break;
            }
        }

        if (!$found) {
            $data[] = $this->mapFromUser($user);
        }

        $this->storage->write($data);

        return $user;
    }

    private function mapToUser(array $row): User
    {
        return new User(
            id: $row['id'],
            email: $row['email'],
            password: $row['password'],
            firstName: $row['first_name'],
            lastName: $row['last_name'],
            creationDate: new DateTimeImmutable($row['creation_date'])
        );
    }

    private function mapFromUser(User $user): array
    {
        return [
            'id' => $user->getUserId(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'creation_date' => $user->getCreationDate()->format('Y-m-d H:i:s'),
        ];
    }
}

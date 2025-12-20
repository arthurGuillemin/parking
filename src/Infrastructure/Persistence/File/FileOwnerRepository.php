<?php

namespace App\Infrastructure\Persistence\File;

use App\Domain\Entity\Owner;
use App\Domain\Repository\OwnerRepositoryInterface;
use App\Infrastructure\Storage\FileStorage;
use DateTimeImmutable;


class FileOwnerRepository implements OwnerRepositoryInterface
{
    private FileStorage $storage;

    public function __construct()
    {
        $this->storage = new FileStorage(__DIR__ . '/storage/owners.json');
    }


    public function findById(string $id): ?Owner
    {
        foreach ($this->storage->read() as $row) {
            if ($row['id'] === $id) {
                return $this->mapToOwner($row);
            }
        }

        return null;
    }


    public function findByEmail(string $email): ?Owner
    {
        foreach ($this->storage->read() as $row) {
            if ($row['email'] === $email) {
                return $this->mapToOwner($row);
            }
        }

        return null;
    }


    public function save(Owner $owner): Owner
    {
        $data = $this->storage->read();
        $found = false;

        foreach ($data as &$row) {
            if ($row['id'] === $owner->getOwnerId()) {
                $row = $this->mapFromOwner($owner);
                $found = true;
                break;
            }
        }

        if (!$found) {
            $data[] = $this->mapFromOwner($owner);
        }

        $this->storage->write($data);

        return $owner;
    }


    private function mapToOwner(array $row): Owner
    {
        return new Owner(
            id: $row['id'],
            email: $row['email'],
            password: $row['password'],
            firstName: $row['first_name'],
            lastName: $row['last_name'],
            creationDate: new DateTimeImmutable($row['creation_date'])
        );
    }

    private function mapFromOwner(Owner $owner): array
    {
        return [
            'id' => $owner->getOwnerId(),
            'email' => $owner->getEmail(),
            'password' => $owner->getPassword(),
            'first_name' => $owner->getFirstName(),
            'last_name' => $owner->getLastName(),
            'creation_date' => $owner->getCreationDate()->format('Y-m-d H:i:s'),
        ];
    }
}

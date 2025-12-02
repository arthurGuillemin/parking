<?php

namespace App\Infrastructure\Persistence\Sql;

use App\Domain\Entity\Owner;
use App\Domain\Repository\OwnerRepositoryInterface;
use App\Infrastructure\Database\Database;
use PDO;
use DateTimeImmutable;
use PDOException;
use RuntimeException;

class SqlOwnerRepository implements OwnerRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    //trouver un owner avec son id

    public function findById(string $id): ?Owner
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, email, password, first_name, last_name, creation_date
                FROM owners
                WHERE id = :id
            ");
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch();
            if (!$row) {
                return null;
            }
            return $this->mapToOwner($row);
        } catch (PDOException $e) {
            throw new RuntimeException("aucun proprietaire de parking trouvé par son id : " . $e->getMessage());
        }
    }
    //trouver un owner avec son email
    public function findByEmail(string $email): ?Owner
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, email, password, first_name, last_name, creation_date
                FROM owners
                WHERE email = :email
            ");
            $stmt->execute(['email' => $email]);
            $row = $stmt->fetch();
            if (!$row) {
                return null;
            }
            return $this->mapToOwner($row);
        } catch (PDOException $e) {
            throw new RuntimeException("aucun proprietaire de parking trouvé par son email: " . $e->getMessage());
        }
    }
    // enregistrer un owner
    public function save(Owner $owner): Owner
    {
        try {
            $existing = $this->findById($owner->getOwnerId());
            if ($existing) {
                $stmt = $this->db->prepare("
                    UPDATE owners
                    SET email = :email,
                        password = :password,
                        first_name = :first_name,
                        last_name = :last_name,
                        creation_date = :creation_date
                    WHERE id = :id
                ");
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO owners (id, email, password, first_name, last_name, creation_date)
                    VALUES (:id, :email, :password, :first_name, :last_name, :creation_date)
                ");
            }
            $stmt->execute([
                'id'            => $owner->getOwnerId(),
                'email'         => $owner->getEmail(),
                'password'      => $owner->getPassword(),
                'first_name'    => $owner->getFirstName(),
                'last_name'     => $owner->getLastName(),
                'creation_date' => $owner->getCreationDate()->format('Y-m-d H:i:s'),
            ]);
            return $owner;
        } catch (PDOException $e) {
            throw new RuntimeException(" echec du save de l'owner: " . $e->getMessage());
        }
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
}

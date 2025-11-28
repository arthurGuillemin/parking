<?php

namespace App\Infrastructure\Persistence\Sql;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Infrastructure\Database\Database;
use PDO;
use PDOException;
use RuntimeException;
use DateTimeImmutable;

class SqlUserRepository implements UserRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    //trouver un user avec son id
    public function findById(string $id): ?User
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, email, password, first_name, last_name, creation_date
                FROM users
                WHERE id = :id
            ");
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch();

            if (!$row) return null;

            return $this->mapToUser($row);

        } catch (PDOException $e) {
            throw new RuntimeException("aucun user touvé avec cet id: " . $e->getMessage());
        }
    }

        //trouver un user avec son email

    public function findByEmail(string $email): ?User
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, email, password, first_name, last_name, creation_date
                FROM users
                WHERE email = :email
            ");
            $stmt->execute(['email' => $email]);
            $row = $stmt->fetch();

            if (!$row) return null;

            return $this->mapToUser($row);

        } catch (PDOException $e) {
            throw new RuntimeException("aucun user toruvé avevc cet email: " . $e->getMessage());
        }
    }
        //save un user 

    public function save(User $user): User
    {
        try {
            $existing = $this->findById($user->getUserId());

            if ($existing) {
                $stmt = $this->db->prepare("
                    UPDATE users
                    SET email = :email,
                        password = :password,
                        first_name = :first_name,
                        last_name = :last_name
                    WHERE id = :id
                ");
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO users (id, email, password, first_name, last_name, creation_date)
                    VALUES (:id, :email, :password, :first_name, :last_name, :creation_date)
                ");
            }

            $stmt->execute([
                'id' => $user->getUserId(),
                'email' => $user->getEmail(),
                'password' => $user->getPassword(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'creation_date' => $user->getCreationDate()->format('Y-m-d H:i:s'),
            ]);

            return $user;

        } catch (PDOException $e) {
            throw new RuntimeException("erreur dans le save de l'user: " . $e->getMessage());
        }
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
}

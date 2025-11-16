<?php

namespace App\Domaine\Entity;

class Owner {
    private string $id; // UUID
    private string $email;
    private string $password;
    private string $firstName;
    private string $lastName;
    private \DateTimeImmutable $creationDate;

    public function __construct(string $id, string $email, string $password, string $firstName, string $lastName, \DateTimeImmutable $creationDate) {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->creationDate = $creationDate;
    }

    public function getOwnerId(): string {
        return $this->id;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function getFirstName(): string {
        return $this->firstName;
    }

    public function getLastName(): string {
        return $this->lastName;
    }

    public function getCreationDate(): \DateTimeImmutable {
        return $this->creationDate;
    }
}

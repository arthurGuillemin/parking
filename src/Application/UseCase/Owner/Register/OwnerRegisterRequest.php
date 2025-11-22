<?php

namespace App\Application\UseCase\Owner\Register;

class OwnerRegisterRequest
{
    public string $email;
    public string $password;
    public string $firstName;
    public string $lastName;

    public function __construct(string $email, string $password, string $firstName, string $lastName)
    {
        $this->email = $email;
        $this->password = $password;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }
}

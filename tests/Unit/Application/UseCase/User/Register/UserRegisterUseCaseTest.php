<?php

declare(strict_types=1);

namespace Unit\Application\UseCase\User\Register;

use App\Application\UseCase\User\Register\UserRegisterRequest;
use App\Application\UseCase\User\Register\UserRegisterUseCase;
use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Security\PasswordHasherInterface;
use App\Domain\Service\UserRegistrationValidator;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class UserRegisterUseCaseTest extends TestCase
{
    public function testSuccessfulRegistrationCreatesUserAndHashesPassword(): void
    {
        $validator = new UserRegistrationValidator();

        // Fake repository en mémoire
        $repo = new class implements UserRepositoryInterface {
            public ?User $savedUser = null;

            public function findById(string $id): ?User
            {
                return null;
            }

            public function findByEmail(string $email): ?User
            {
                // Aucun utilisateur existant pour cet email
                return null;
            }

            public function save(User $user): User
            {
                $this->savedUser = $user;

                // On simule la génération d'un ID par la BDD
                return new User(
                    $user->getUserId(),
                    $user->getEmail(),
                    $user->getPassword(),
                    $user->getFirstName(),
                    $user->getLastName(),
                    $user->getCreationDate()
                );
            }
        };

        // Fake PasswordHasher
        $hasher = new class implements PasswordHasherInterface {
            public function hash(string $password): string
            {
                return 'hashed:' . $password;
            }
            public function verify(string $password, string $hash): bool
            {
                return true;
            }
        };

        $useCase = new UserRegisterUseCase(
            userRepository: $repo,
            validator: $validator,
            passwordHasher: $hasher
        );

        $request = new UserRegisterRequest(
            email: 'user@example.com',
            password: 'Password123',
            firstName: 'Harvyn',
            lastName: 'Dev'
        );

        $user = $useCase->execute($request);

        // Assertions
        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('user@example.com', $user->getEmail());
        $this->assertStringStartsWith('hashed:', $user->getPassword());

        $this->assertNotNull($repo->savedUser, 'L’utilisateur devrait être sauvegardé dans le repository.');
        $this->assertSame($user->getEmail(), $repo->savedUser->getEmail());
    }

    public function testRegistrationFailsWhenEmailAlreadyExists(): void
    {
        $validator = new UserRegistrationValidator();

        // Repo qui renvoie déjà un User pour cet email
        $repo = new class implements UserRepositoryInterface {
            public function findById(string $id): ?User
            {
                return null;
            }

            public function findByEmail(string $email): ?User
            {
                return new User(
                    'existing-id',
                    $email,
                    'hash',
                    'Existing',
                    'User',
                    new \DateTimeImmutable()
                );
            }

            public function save(User $user): User
            {
                throw new \LogicException('save() ne devrait pas être appelé si l\'email existe déjà.');
            }
        };

        $hasher = new class implements PasswordHasherInterface {
            public function hash(string $password): string
            {
                return 'hashed:' . $password;
            }
            public function verify(string $password, string $hash): bool
            {
                return true;
            }
        };

        $useCase = new UserRegisterUseCase(
            userRepository: $repo,
            validator: $validator,
            passwordHasher: $hasher
        );

        $request = new UserRegisterRequest(
            email: 'user@example.com',
            password: 'Password123',
            firstName: 'New',
            lastName: 'User'
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Un compte avec cet email existe déjà.');

        $useCase->execute($request);
    }

    public function testRegistrationFailsWithInvalidData(): void
    {
        $validator = new UserRegistrationValidator();

        $repo = new class implements UserRepositoryInterface {
            public function findById(string $id): ?User
            {
                return null;
            }

            public function findByEmail(string $email): ?User
            {
                return null;
            }

            public function save(User $user): User
            {
                throw new \LogicException('save() ne devrait pas être appelé si les données sont invalides.');
            }
        };

        $hasher = new class implements PasswordHasherInterface {
            public function hash(string $password): string
            {
                return 'hashed:' . $password;
            }
            public function verify(string $password, string $hash): bool
            {
                return true;
            }
        };

        $useCase = new UserRegisterUseCase(
            userRepository: $repo,
            validator: $validator,
            passwordHasher: $hasher
        );

        // Email invalide + mot de passe trop court
        $request = new UserRegisterRequest(
            email: 'not-an-email',
            password: '123',
            firstName: '',
            lastName: ''
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Données d\'inscription invalides');

        $useCase->execute($request);
    }
}

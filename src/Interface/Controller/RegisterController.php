<?php
namespace App\Interface\Controller;

use App\Application\UseCase\User\Register\UserRegisterUseCase;
use App\Application\UseCase\User\Register\UserRegisterRequest;
use App\Domain\Security\XssProtectionService;

class RegisterController
{
    private UserRegisterUseCase $registerUseCase;
    private XssProtectionService $xssProtection;

    public function __construct(UserRegisterUseCase $registerUseCase, XssProtectionService $xssProtection)
    {
        $this->registerUseCase = $registerUseCase;
        $this->xssProtection = $xssProtection;
    }

    public function registerForm(): void
    {
        header('Content-Type: text/html; charset=UTF-8');
        require __DIR__ . '/../../../templates/register_form.php';
    }

    /**
     * Gère l'inscription d'un nouvel utilisateur
     */
    public function register(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        $rawData = $this->parseRegistrationInput();
        $sanitizedData = $this->sanitizeRegistrationData($rawData);

        if (!$this->validateRegistrationFields($sanitizedData, $rawData['password'], $rawData['passwordConfirm'])) {
            return;
        }

        try {
            $request = new UserRegisterRequest(
                $sanitizedData['email'],
                $rawData['password'],
                $sanitizedData['firstName'],
                $sanitizedData['lastName']
            );
            $user = $this->registerUseCase->execute($request);
            $this->sendSuccessResponse($user);
        } catch (\InvalidArgumentException $e) {
            $this->sendErrorResponse(400, $e->getMessage());
        } catch (\Exception $e) {
            $this->handleRegistrationError($e);
        }
    }

    /**
     * Parse les données d'entrée (JSON ou POST)
     */
    private function parseRegistrationInput(): array
    {
        $input = file_get_contents('php://input');
        $jsonData = json_decode($input, true) ?? [];

        return [
            'email' => $jsonData['email'] ?? $_POST['email'] ?? '',
            'password' => $jsonData['password'] ?? $_POST['password'] ?? '',
            'passwordConfirm' => $jsonData['passwordConfirm'] ?? $_POST['passwordConfirm'] ?? '',
            'firstName' => $jsonData['firstName'] ?? $_POST['firstName'] ?? $_POST['first_name'] ?? '',
            'lastName' => $jsonData['lastName'] ?? $_POST['lastName'] ?? $_POST['last_name'] ?? '',
        ];
    }

    /**
     * Applique la protection XSS sur les données
     */
    private function sanitizeRegistrationData(array $rawData): array
    {
        return [
            'email' => $this->xssProtection->sanitizeEmail($rawData['email']),
            'firstName' => $this->xssProtection->sanitize($rawData['firstName']),
            'lastName' => $this->xssProtection->sanitize($rawData['lastName']),
        ];
    }

    /**
     * Valide les champs requis pour l'inscription
     */
    private function validateRegistrationFields(array $data, string $password, string $passwordConfirm): bool
    {
        if (!$data['email']) {
            $this->sendErrorResponse(400, 'Format d\'email invalide');
            return false;
        }
        if (empty($data['firstName'])) {
            $this->sendErrorResponse(400, 'Le prénom est obligatoire');
            return false;
        }
        if (empty($data['lastName'])) {
            $this->sendErrorResponse(400, 'Le nom est obligatoire');
            return false;
        }
        if (!$this->isValidPassword($password)) {
            $this->sendErrorResponse(400, 'Le mot de passe doit contenir au moins 8 caractères');
            return false;
        }
        if ($password !== $passwordConfirm) {
            $this->sendErrorResponse(400, 'Les mots de passe ne correspondent pas');
            return false;
        }
        return true;
    }

    /**
     * Envoie la réponse de succès après inscription
     */
    private function sendSuccessResponse($user): void
    {
        http_response_code(201);
        echo json_encode([
            'message' => 'Utilisateur créé avec succès',
            'id' => $user->getUserId(),
            'email' => $user->getEmail()
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Gère les erreurs lors de l'inscription
     */
    private function handleRegistrationError(\Exception $e): void
    {
        error_log('Registration error: ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());

        $errorMessage = 'Une erreur est survenue lors de l\'inscription';
        if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
            $errorMessage .= ': ' . $e->getMessage();
        }
        $this->sendErrorResponse(500, $errorMessage);
    }

    private function isValidPassword(string $password): bool
    {
        return strlen($password) >= 8;
    }

    private function sendErrorResponse(int $code, string $message): void
    {
        http_response_code($code);
        $safeMessage = $this->xssProtection->sanitize($message);
        echo json_encode(['error' => $safeMessage], JSON_UNESCAPED_UNICODE);
    }
}

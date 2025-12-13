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

    public function register(): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        
        // Récupération des données (JSON ou POST)
        $input = file_get_contents('php://input');
        $jsonData = json_decode($input, true);
        
        // Récupération et nettoyage des données avec protection anti-XSS
        $rawEmail = $jsonData['email'] ?? $_POST['email'] ?? '';
        $password = $jsonData['password'] ?? $_POST['password'] ?? '';
        $passwordConfirm = $jsonData['passwordConfirm'] ?? $_POST['passwordConfirm'] ?? '';
        $rawFirstName = $jsonData['firstName'] ?? $_POST['firstName'] ?? $_POST['first_name'] ?? '';
        $rawLastName = $jsonData['lastName'] ?? $_POST['lastName'] ?? $_POST['last_name'] ?? '';
        
        // Protection anti-XSS
        $email = $this->xssProtection->sanitizeEmail($rawEmail);
        $firstName = $this->xssProtection->sanitize($rawFirstName);
        $lastName = $this->xssProtection->sanitize($rawLastName);
        
        // Validation des champs requis
        if (!$email) {
            $this->sendErrorResponse(400, 'Format d\'email invalide');
            return;
        }
        
        if (empty($firstName)) {
            $this->sendErrorResponse(400, 'Le prénom est obligatoire');
            return;
        }
        
        if (empty($lastName)) {
            $this->sendErrorResponse(400, 'Le nom est obligatoire');
            return;
        }
        
        if (!$this->isValidPassword($password)) {
            $this->sendErrorResponse(400, 'Le mot de passe doit contenir au moins 8 caractères');
            return;
        }
        
        // Validation de la confirmation de mot de passe
        if ($password !== $passwordConfirm) {
            $this->sendErrorResponse(400, 'Les mots de passe ne correspondent pas');
            return;
        }

        try {
            $request = new UserRegisterRequest($email, $password, $firstName, $lastName);
            $user = $this->registerUseCase->execute($request);
            
            http_response_code(201);
            echo json_encode([
                'message' => 'Utilisateur créé avec succès',
                'id' => $user->getUserId(),
                'email' => $user->getEmail()
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (\InvalidArgumentException $e) {
            $this->sendErrorResponse(400, $e->getMessage());
        } catch (\Exception $e) {
            error_log('Registration error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            $errorMessage = 'Une erreur est survenue lors de l\'inscription';
            // En développement, afficher plus de détails
            if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                $errorMessage .= ': ' . $e->getMessage();
            }
            $this->sendErrorResponse(500, $errorMessage);
        }
    }

    private function isValidPassword(string $password): bool
    {
        return strlen($password) >= 8;
    }

    private function sendErrorResponse(int $code, string $message): void
    {
        http_response_code($code);
        // Protection anti-XSS sur le message d'erreur
        $safeMessage = $this->xssProtection->sanitize($message);
        echo json_encode(['error' => $safeMessage], JSON_UNESCAPED_UNICODE);
    }
}

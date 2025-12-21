<?php

namespace App\Interface\Controller;

use App\Application\UseCase\User\GetInvoice\GetInvoiceUseCase;
use App\Application\UseCase\User\GetInvoice\GetInvoiceRequest;
use App\Domain\Service\JwtService;
use App\Domain\Repository\UserRepositoryInterface;

class InvoiceController
{
    private GetInvoiceUseCase $getInvoiceUseCase;
    private JwtService $jwtService;
    private UserRepositoryInterface $userRepository;

    public function __construct(GetInvoiceUseCase $getInvoiceUseCase, JwtService $jwtService, UserRepositoryInterface $userRepository)
    {
        $this->getInvoiceUseCase = $getInvoiceUseCase;
        $this->jwtService = $jwtService;
        $this->userRepository = $userRepository;
    }

    public function download(array $args): void
    {
        // 1. Check Auth
        $token = $_COOKIE['auth_token'] ?? null;
        if (!$token) {
            header('Location: /login');
            exit;
        }
        $userId = $this->jwtService->validateToken($token);
        if (!$userId) {
            header('Location: /login');
            exit;
        }

        // 2. Get Invoice
        $invoiceId = (int) $args['id'];
        $request = new GetInvoiceRequest($invoiceId, $userId);
        $invoice = $this->getInvoiceUseCase->execute($request);

        if (!$invoice) {
            http_response_code(404);
            echo "Facture introuvable ou accès refusé.";
            exit;
        }

        // 3. Get User Details
        $user = $this->userRepository->findById($userId);
        $userFullName = $user ? $user->getFirstName() . ' ' . $user->getLastName() : 'Client Inconnu';

        // 4. Render Template
        require __DIR__ . '/../../../templates/invoice.php';
    }
}

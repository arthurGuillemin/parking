<?php
namespace App\Interface\Presenter\Auth;

class LoginHtmlPresenter
{
    public function present($response): string
    {
        ob_start();
        $error = ($response === null);
        include __DIR__ . '/../../../../templates/login_form.php';
        return ob_get_clean();
    }
}


<?php

namespace App\Interface\Controller;

class HomeController
{
    public function index()
    {
        require dirname(__DIR__, 3) . '/templates/home.php';
    }
}

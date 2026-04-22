<?php

declare(strict_types=1);

namespace App\Controllers;

use Marko\Routing\Attributes\Get;

class HomeController
{
    #[Get('/')]
    public function index(): string
    {
        return 'Hello from Marko!';
    }
}
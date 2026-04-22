<?php

declare(strict_types=1);

namespace App\Controllers;

use Marko\Routing\Attributes\Get;
use Marko\Routing\Http\Response;

class HomeController
{
    #[Get('/')]
    public function index(): Response
    {
        return Response::html(View::render('home.phtml', [
            'eyebrow' => 'Marko App',
            'title' => 'Nativa',
            'message' => 'A small Marko application with a shared layout and a simple blog.',
        ]));
    }
}

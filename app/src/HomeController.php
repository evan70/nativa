<?php

declare(strict_types=1);

namespace App;

use Marko\Routing\Attributes\Get;
use Marko\Routing\Http\Response;
use Marko\View\ViewInterface;

class HomeController
{
    public function __construct(
        private readonly ViewInterface $view,
    ) {}

    #[Get('/')]
    public function index(): Response
    {
        return $this->view->render('app/home', [
            'eyebrow' => 'Marko App',
            'title' => 'Nativa',
            'message' => 'A small Marko application with a shared layout and a simple blog.',
        ]);
    }
}

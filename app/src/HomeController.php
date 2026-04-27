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
        $lcpImage = 'https://res.cloudinary.com/epithemic/image/upload/v1773169416/blog/dae2d1fd9b13c89bb5b4a89280099d7a_hqfarh.webp';
        
        if ($this->view instanceof ViewAdapter) {
            $this->view->withLcpImage($lcpImage);
        }

        return $this->view->render('app.home', [
            'eyebrow' => 'Marko App',
            'title' => 'Nativa',
            'message' => 'A small Marko application with a shared layout and a simple blog.',
        ]);
    }
}

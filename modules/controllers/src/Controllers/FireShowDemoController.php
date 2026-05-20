<?php

declare(strict_types=1);

namespace App\Controllers;

use Marko\Routing\Attributes\Get;
use Marko\Routing\Http\Response;
use Marko\View\ViewInterface;

class FireShowDemoController
{
    public function __construct(private readonly ViewInterface $view) {}

    #[Get(path: '/fire-show-demo')]
    public function index(): Response
    {
        return $this->view
            ->render('pages/fire-show-demo/template', [
                'title' => 'Fire Show Demo - All 12 Features',
                'metaDescription' => 'Demo page showcasing all 12 interactive features of the Fire Show theme.',
            ]);
    }
}
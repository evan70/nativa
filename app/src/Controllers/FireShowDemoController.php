<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\AssetAwareViewInterface;
use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;
use Marko\Routing\Attributes\Get;

class FireShowDemoController
{
    public function __construct(private readonly AssetAwareViewInterface $view) {}

    #[Get(path: '/fire-show-demo')]
    public function index(Request $request): Response
    {
        return $this->view
            ->withTitle('Fire Show Demo - All 12 Features')
            ->withMetaDescription('Demo page showcasing all 12 interactive features of the Fire Show theme.')
            ->render('pages/fire-show-demo/template', [
                'title' => 'Fire Show Demo',
            ]);
    }
}
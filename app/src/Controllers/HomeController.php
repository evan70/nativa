<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\AssetAwareViewInterface;
use App\View;
use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;
use Marko\Routing\Attributes\Get;
use Marko\View\ViewInterface;

class HomeController
{
    public function __construct(private readonly AssetAwareViewInterface $view) {}

    #[Get(path: '/')]
    public function index(Request $request): Response
    {
        if ($this->shouldLogDebug()) {
            $this->logDebug('HomeController::index called', ['path' => $request->path()]);
        }
        
        // LCP: Hero background image (preloaded for above-the-fold)
        return $this->view
            ->withLcpImage('https://res.cloudinary.com/epithemic/image/upload/v1773169416/blog/dae2d1fd9b13c89bb5b4a89280099d7a_hqfarh.webp')
            ->render('pages/home/template', [
                'eyebrow' => 'Nativa',
                'title' => 'Welcome to Nativa',
                'metaDescription' => 'Nativa - Building the next generation of web applications with vanilla performance and BEM architecture.',
                'message' => 'Hello, Marko!',
            ]);
    }

    private function shouldLogDebug(): bool
    {
        return getenv('LOG_LEVEL') === 'debug';
    }

    /** @param array<string, mixed> $context */
    private function logDebug(string $message, array $context = []): void
    {
        if (function_exists('log_add_debug')) {
            log_add_debug($message, $context);
        }
    }
}

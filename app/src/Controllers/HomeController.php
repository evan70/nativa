<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\AssetAwareViewInterface;
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
        
        return $this->view
            ->render('pages/home/template', [
                'eyebrow' => 'Nativa',
                'title' => 'Welcome to Nativa',
                'message' => 'Hello, Marko!',
            ]);
    }

    private function shouldLogDebug(): bool
    {
        return getenv('LOG_LEVEL') === 'debug';
    }

    private function logDebug(string $message, array $context = []): void
    {
        if (function_exists('log_add_debug')) {
            log_add_debug($message, $context);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers;

use Marko\Http\Request;
use Marko\Http\Response;

class HomeController
{
    public function index(Request $request): Response
    {
        if ($this->shouldLogDebug()) {
            $this->logDebug('HomeController::index called', ['path' => $request->path()]);
        }
        return new Response('Hello, Marko!');
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

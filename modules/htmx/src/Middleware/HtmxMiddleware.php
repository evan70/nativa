<?php

declare(strict_types=1);

namespace App\Htmx\Middleware;

use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;
use Marko\Routing\Middleware\MiddlewareInterface;

readonly class HtmxMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $response = $next($request);

        if ($request->header('HX-Request') !== 'true') {
            return $response;
        }

        return $response->withHeader('Vary', 'HX-Request');
    }
}
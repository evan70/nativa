<?php

declare(strict_types=1);

namespace App\Middleware;

use Marko\Authentication\AuthManager;
use Marko\Authentication\Guard\TokenGuard;
use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;
use Marko\Routing\Middleware\MiddlewareInterface;

/**
 * Admin authentication middleware - redirects to /mark/login instead of /login
 */
readonly class AdminAuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AuthManager $auth,
        private ?string $guard = null,
    ) {}

    public function handle(
        Request $request,
        callable $next,
    ): Response {
        $guard = $this->auth->guard($this->guard);

        if ($guard->check()) {
            return $next($request);
        }

        // API guards return JSON 401
        if ($guard instanceof TokenGuard) {
            return Response::json(
                data: ['error' => 'Unauthorized'],
                statusCode: 401,
            );
        }

        // Redirect to admin login page
        return Response::redirect('/mark/login');
    }
}
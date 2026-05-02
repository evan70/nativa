<?php

declare(strict_types=1);

namespace App\Init\Middleware;

use Marko\Routing\Http\Request;
use Marko\Routing\Middleware\MiddlewareInterface;
use App\Init\Module\ModuleGroupManagerInterface;

/**
 * Blocks routes if their module group is not active.
 * Enable in config/module.php: 'group_middleware' => true
 */
readonly class GroupRouteGuard implements MiddlewareInterface
{
    public function __construct(
        private ?ModuleGroupManagerInterface $groupManager = null,
    ) {}

    public function process(
        Request $request,
        callable $next,
    ): \Marko\Routing\Http\Response {
        if ($this->groupManager === null) {
            return $next($request);
        }

        $path = $request->getPath();
        $groupName = $this->groupManager->getGroupForRoute($path);

        // No group for this route - allow
        if ($groupName === null) {
            return $next($request);
        }

        // Group is active - allow
        if ($this->groupManager->isGroupActive($groupName)) {
            return $next($request);
        }

        // Group not active - return 404
        return new \Marko\Routing\Http\Response('Not Found', 404);
    }
}

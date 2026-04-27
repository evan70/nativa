<?php

declare(strict_types=1);

namespace Marko\Amphp;

use Closure;
use Amp\Future;
use function Amp\async;

class EventLoopRunner
{
    /**
     * Run a task asynchronously.
     */
    public function run(Closure $callback): Future
    {
        return async($callback);
    }
}

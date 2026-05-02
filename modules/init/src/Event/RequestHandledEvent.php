<?php

declare(strict_types=1);

namespace App\Init\Module;

use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;

/**
 * Event dispatched after a request has been handled.
 */
readonly class RequestHandledEvent
{
    public function __construct(
        public Request $request,
        public Response $response,
    ) {}
}

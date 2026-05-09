<?php

declare(strict_types=1);

use App\Htmx\Middleware\HtmxMiddleware;

return [
    'middleware' => [
        HtmxMiddleware::class,
    ],
];
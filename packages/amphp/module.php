<?php

declare(strict_types=1);

namespace Marko\Amphp;

return [
    'bindings' => [
        AmphpConfig::class => AmphpConfig::class,
        EventLoopRunner::class => EventLoopRunner::class,
    ],
];

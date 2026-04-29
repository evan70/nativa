<?php

declare(strict_types=1);

return [
    'driver' => 'file',
    'lifetime' => 120,
    'expire_on_close' => false,
    'path' => dirname(__DIR__) . '/storage/framework/sessions',
    'cookie' => [
        'name' => 'marko_session',
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'http_only' => true,
    ],
];

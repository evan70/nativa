<?php

declare(strict_types=1);

return [
    'driver' => 'log',
    'from' => [
        'address' => 'noreply@marko.local',
        'name' => 'Marko App',
    ],
    'log' => [
        'path' => dirname(__DIR__) . '/storage/logs/mail.log',
    ],
];

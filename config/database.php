<?php

declare(strict_types=1);

return [
    'driver' => 'sqlite',
    'database' => dirname(__DIR__) . '/storage/data/database.sqlite',
    
    // Module-specific databases
    'connections' => [
        'blog' => [
            'driver' => 'sqlite',
            'database' => dirname(__DIR__) . '/storage/data/articles.db',
        ],
    ],
];

<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Module Groups Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for module groups and idle eviction.
    |
    */
    
    'eviction' => [
        // Enable/disable idle eviction
        'enabled' => true,
        
        // Default idle timeout for groups without explicit timeout
        'default' => '5m', // 5 minutes
        
        // Check interval - how often to check for idle groups
        'check_interval' => '1m', // 1 minute
    ],
    
    // Default routes that trigger group activation
    // These routes will auto-activate their groups
    'auto_activate_routes' => [
        '/admin/*',
        '/mark/*',
        '/blog/*',
    ],
];

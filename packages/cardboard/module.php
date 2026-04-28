<?php

declare(strict_types=1);

use Marko\Cardboard\Config\AdminPanelConfig;
use Marko\Cardboard\Config\AdminPanelConfigInterface;

return [
    'bindings' => [
        AdminPanelConfigInterface::class => AdminPanelConfig::class,
    ],
];

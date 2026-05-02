<?php

declare(strict_types=1);

use App\Init\Module\ModuleGroupManager;
use App\Init\Module\ModuleGroupManagerInterface;
use Marko\Core\Container\ContainerInterface;

/**
 * Bootstrap for init module - registers ModuleGroupManager if group metadata exists.
 */
return [
    'singletons' => [
        ModuleGroupManagerInterface::class => function (ContainerInterface $container): ModuleGroupManagerInterface {
            $manager = new ModuleGroupManager(
                $container,
                '5m',
                true,
            );

            return $manager;
        },
    ],

    'boot' => function (ContainerInterface $container): void {
        // Register groups from all modules
        $app = $container->get(\Marko\Core\Application::class);
        $manager = $container->get(ModuleGroupManagerInterface::class);

        foreach ($app->modules as $module) {
            $manager->registerGroup($module);
        }
    },
];
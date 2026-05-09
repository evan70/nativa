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
            // Get config if available
            $config = [];
            if ($container->has(\Marko\Config\ConfigRepositoryInterface::class)) {
                $configRepo = $container->get(\Marko\Config\ConfigRepositoryInterface::class);
                $config = $configRepo->get('module') ?? [];
            }

            $evictionConfig = $config['eviction'] ?? [];
            $defaultTimeout = $evictionConfig['default'] ?? '5m';
            $enabled = $evictionConfig['enabled'] ?? true;

            $manager = new ModuleGroupManager(
                $container,
                $defaultTimeout,
                $enabled,
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

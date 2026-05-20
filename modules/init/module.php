<?php

declare(strict_types=1);

use App\Init\Bootstrap\TemplateResolver;
use App\Init\Module\ModuleGroupManager;
use App\Init\Module\ModuleGroupManagerInterface;
use Marko\Core\Application;
use Marko\Core\Container\ContainerInterface;
use Marko\View\ModuleTemplateResolver;
use Marko\View\TemplateResolverInterface;

/**
 * Bootstrap for init module.
 *
 * Overrides:
 * - TemplateResolverInterface: adds templates/ directory fallback
 * - ModuleGroupManagerInterface: provides group-based idle eviction
 */
return [
    'bindings' => [
        TemplateResolverInterface::class => function (ContainerInterface $container): TemplateResolverInterface {
            /** @var ModuleTemplateResolver $upstream */
            $upstream = $container->get(ModuleTemplateResolver::class);
            /** @var Application $app */
            $app = $container->get(Application::class);

            return new TemplateResolver(
                upstream: $upstream,
                basePath: dirname($app->vendorPath),
            );
        },
    ],

    'singletons' => [
        ModuleGroupManagerInterface::class => function (ContainerInterface $container): ModuleGroupManagerInterface {
            // Get config if available
            $config = [];
            if ($container->has(\Marko\Config\ConfigRepositoryInterface::class)) {
                /** @var \Marko\Config\ConfigRepositoryInterface $configRepo */
                $configRepo = $container->get(\Marko\Config\ConfigRepositoryInterface::class);
                /** @var array{eviction?: array{default?: string, enabled?: bool}} $config */
                $config = $configRepo->get('module') ?? [];
            }

            $evictionConfig = $config['eviction'] ?? [];
            /** @var string $defaultTimeout */
            $defaultTimeout = $evictionConfig['default'] ?? '5m';
            /** @var bool $enabled */
            $enabled = $evictionConfig['enabled'] ?? true;

            /** @var \Marko\Core\Container\Container $container */
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
        /** @var Application $app */
        $app = $container->get(Application::class);
        /** @var ModuleGroupManagerInterface $manager */
        $manager = $container->get(ModuleGroupManagerInterface::class);

        foreach ($app->modules as $module) {
            $manager->registerGroup($module);
        }
    },
];

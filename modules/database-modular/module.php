<?php

declare(strict_types=1);

use App\AppLogger;
use App\Database\CardboardConnection;
use App\DatabaseModular\Contracts\ModuleDatabaseResolverInterface;
use App\DatabaseModular\ModuleDatabaseResolver;
use Marko\Core\Container\ContainerInterface;

return [
    'singletons' => [
        ModuleDatabaseResolverInterface::class => function (ContainerInterface $container): ModuleDatabaseResolverInterface {
            /** @var \Marko\Config\ConfigRepositoryInterface $config */
            $config = $container->get(\Marko\Config\ConfigRepositoryInterface::class);

            // Module mapping - maps module name to database name
            $mapping = $config->getArray('database.modules');
            $storagePath = dirname(__DIR__, 2) . '/storage/data';

            AppLogger::debug('[DatabaseModular] Initialized with mapping: ' . json_encode($mapping));

            return new ModuleDatabaseResolver($mapping, $storagePath);
        },
        
        // CardboardConnection - for main database access (users, roles, settings)
        // Note: resolver is singleton, so this closure is called only once
        CardboardConnection::class => function (ContainerInterface $container): CardboardConnection {
            AppLogger::debug('[DatabaseModular] Binding CardboardConnection (singleton)');

            // Get already-initialized singleton resolver
            $resolver = $container->get(ModuleDatabaseResolverInterface::class);

            return new CardboardConnection($resolver);
        },
    ],
];
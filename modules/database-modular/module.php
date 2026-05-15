<?php

declare(strict_types=1);

use App\Database\NativaConnection;
use App\DatabaseModular\Contracts\ModuleDatabaseResolverInterface;
use App\DatabaseModular\ModuleDatabaseResolver;
use Marko\Core\Container\ContainerInterface;

return [
    'bindings' => [
        ModuleDatabaseResolverInterface::class => function (ContainerInterface $container): ModuleDatabaseResolverInterface {
            /** @var \Marko\Config\ConfigRepositoryInterface $config */
            $config = $container->get(\Marko\Config\ConfigRepositoryInterface::class);

            // Module mapping - maps module name to database name
            $mapping = $config->getArray('database.modules');
            $storagePath = dirname(__DIR__, 2) . '/storage/data';

            error_log('[DatabaseModular] Initialized with mapping: ' . json_encode($mapping));

            return new ModuleDatabaseResolver($mapping, $storagePath);
        },
        
        // NativaConnection - for system database access
        NativaConnection::class => function (ContainerInterface $container): NativaConnection {
            $resolver = $container->get(ModuleDatabaseResolverInterface::class);
            
            error_log('[DatabaseModular] Binding NativaConnection');
            
            return new NativaConnection($resolver);
        },
    ],
];
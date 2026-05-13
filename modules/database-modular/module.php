<?php

declare(strict_types=1);

use App\Database\NativaConnection;
use App\DatabaseModular\Contracts\ModuleDatabaseResolverInterface;
use App\DatabaseModular\ModuleDatabaseResolver;
use Marko\Core\Container\ContainerInterface;

return [
    'bindings' => [
        ModuleDatabaseResolverInterface::class => function (): ModuleDatabaseResolverInterface {
            // Module mapping - maps module name to database name
            $mapping = [
                'blog' => 'articles',
                'cardboard' => 'cardboard',
                'nativa' => 'nativa',  // System database for users, roles, permissions, sessions
            ];
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
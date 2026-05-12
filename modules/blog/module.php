<?php

declare(strict_types=1);

use App\Blog\Admin\BlogAdminSection;
use App\Blog\Contracts\ArticleServiceInterface;
use App\Blog\Controller\BlogAdminController;
use App\Blog\Database\ArticleConnection;
use App\Blog\Repository\ArticleRepository;
use App\Blog\Service\ArticleService;
use Marko\Admin\Contracts\AdminSectionRegistryInterface;
use Marko\Core\Container\ContainerInterface;
use Marko\Database\Entity\EntityHydrator;
use Marko\Database\Entity\EntityMetadataFactory;
use Marko\Log\Contracts\LoggerInterface;

return [
    'bindings' => [
        ArticleConnection::class => function (): ArticleConnection {
            return new ArticleConnection();
        },
        
        ArticleRepository::class => function (ContainerInterface $container): ArticleRepository {
            return new ArticleRepository(
                $container->get(ArticleConnection::class)->getConnection(),
                new EntityMetadataFactory(),
                new EntityHydrator(),
            );
        },
        ArticleServiceInterface::class => function (ContainerInterface $container): ArticleServiceInterface {
            $repository = $container->get(ArticleRepository::class);
            
            // Optional logger - won't fail if not bound
            $logger = null;
            if ($container->has(LoggerInterface::class)) {
                $logger = $container->get(LoggerInterface::class);
            }
            
            return new ArticleService($repository, $logger);
        },
    ],
    
    'boot' => function (ContainerInterface $container): void {
        // Register admin section
        if ($container->has(AdminSectionRegistryInterface::class)) {
            $registry = $container->get(AdminSectionRegistryInterface::class);
            $registry->register(new BlogAdminSection());
        }
    },
];
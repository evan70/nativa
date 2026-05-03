<?php

declare(strict_types=1);

use App\Blog\Contracts\ArticleServiceInterface;
use App\Blog\Repository\ArticleRepository;
use App\Blog\Service\ArticleService;
use Marko\Core\Container\ContainerInterface;
use Marko\Database\Entity\EntityHydrator;
use Marko\Database\Entity\EntityMetadataFactory;
use Marko\Log\Contracts\LoggerInterface;

return [
    'bindings' => [
        ArticleRepository::class => function (ContainerInterface $container): ArticleRepository {
            return new ArticleRepository(
                $container->get(\Marko\Database\Connection\ConnectionInterface::class),
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
];
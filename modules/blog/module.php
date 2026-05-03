<?php

declare(strict_types=1);

use App\Blog\Contracts\ArticleServiceInterface;
use App\Blog\Repository\ArticleRepository;
use App\Blog\Service\ArticleService;
use Marko\Core\Container\ContainerInterface;
use Marko\Database\Entity\EntityHydrator;
use Marko\Database\Entity\EntityMetadataFactory;

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
            // Get repository from container
            $repository = $container->get(ArticleRepository::class);
            
            // Return service without logger for now (logging can be added when logger is properly bound)
            return new ArticleService($repository);
        },
    ],
];
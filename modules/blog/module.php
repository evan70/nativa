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
            return new ArticleService(
                $container->get(ArticleRepository::class),
                $container->get(LoggerInterface::class),
            );
        },
    ],
];
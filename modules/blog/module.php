<?php

declare(strict_types=1);

use App\Blog\Repository\ArticleRepository;
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
    ],
];
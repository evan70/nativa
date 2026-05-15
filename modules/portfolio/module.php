<?php

declare(strict_types=1);

use App\DatabaseModular\Contracts\ModuleDatabaseResolverInterface;
use App\Portfolio\Database\PortfolioConnection;
use App\Portfolio\Repository\PortfolioItemRepository;
use Marko\Core\Container\ContainerInterface;
use Marko\Database\Entity\EntityHydrator;
use Marko\Database\Entity\EntityMetadataFactory;

return [
    'bindings' => [
        PortfolioConnection::class => function (ContainerInterface $container): PortfolioConnection {
            return new PortfolioConnection(
                $container->get(ModuleDatabaseResolverInterface::class),
            );
        },

        PortfolioItemRepository::class => function (ContainerInterface $container): PortfolioItemRepository {
            return new PortfolioItemRepository(
                $container->get(PortfolioConnection::class)->getConnection(),
                new EntityMetadataFactory(),
                new EntityHydrator(),
            );
        },
    ],
];

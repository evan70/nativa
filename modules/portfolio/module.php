<?php

declare(strict_types=1);

use App\DatabaseModular\Contracts\ModuleDatabaseResolverInterface;
use App\Portfolio\Admin\PortfolioAdminSection;
use App\Portfolio\Database\PortfolioConnection;
use App\Portfolio\Repository\PortfolioItemRepository;
use Marko\Admin\Contracts\AdminSectionRegistryInterface;
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

    'boot' => function (ContainerInterface $container): void {
        if ($container->has(AdminSectionRegistryInterface::class)) {
            $registry = $container->get(AdminSectionRegistryInterface::class);
            $registry->register(new PortfolioAdminSection());
        }
    },
];

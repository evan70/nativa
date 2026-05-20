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
            /** @var ModuleDatabaseResolverInterface $resolver */
            $resolver = $container->get(ModuleDatabaseResolverInterface::class);
            return new PortfolioConnection($resolver);
        },

        PortfolioItemRepository::class => function (ContainerInterface $container): PortfolioItemRepository {
            /** @var PortfolioConnection $portfolioConnection */
            $portfolioConnection = $container->get(PortfolioConnection::class);
            return new PortfolioItemRepository(
                $portfolioConnection->getConnection(),
                new EntityMetadataFactory(),
                new EntityHydrator(),
            );
        },
    ],

    'boot' => function (ContainerInterface $container): void {
        if ($container->has(AdminSectionRegistryInterface::class)) {
            /** @var AdminSectionRegistryInterface $registry */
            $registry = $container->get(AdminSectionRegistryInterface::class);
            $registry->register(new PortfolioAdminSection());
        }
    },
];

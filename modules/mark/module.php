<?php

declare(strict_types=1);

use Marko\Mark\MarkProvider;
use Marko\Mark\Config\MarkConfig;
use Marko\Mark\Config\MarkConfigInterface;
use Marko\Mark\Config\AdminPanelConfig;
use Marko\Mark\Config\AdminPanelConfigInterface;
use Marko\Mark\PermissionRegistry;
use Marko\Mark\Contracts\PermissionRegistryInterface;
use Marko\Mark\Repository\MarkRepository;
use Marko\Mark\Repository\MarkRepositoryInterface;
use Marko\Mark\Repository\PermissionRepository;
use Marko\Mark\Repository\PermissionRepositoryInterface;
use Marko\Mark\Repository\RoleRepository;
use Marko\Mark\Repository\RoleRepositoryInterface;
use Marko\Authentication\Contracts\PasswordHasherInterface;
use Marko\Authentication\Contracts\UserProviderInterface;
use Marko\Core\Container\ContainerInterface;

use Marko\Mark\Database\MarkConnection;
use Marko\Database\Entity\EntityHydrator;
use Marko\Database\Entity\EntityMetadataFactory;

return [
    'bindings' => [
        MarkConfigInterface::class => MarkConfig::class,
        AdminPanelConfigInterface::class => AdminPanelConfig::class,
        
        MarkConnection::class => function (ContainerInterface $container): MarkConnection {
            return new MarkConnection(
                $container->get(\App\DatabaseModular\Contracts\ModuleDatabaseResolverInterface::class)
            );
        },

        MarkRepositoryInterface::class => function (ContainerInterface $container): MarkRepository {
            return new MarkRepository(
                $container->get(MarkConnection::class)->getConnection(),
                $container->get(EntityMetadataFactory::class),
                $container->get(EntityHydrator::class),
            );
        },

        RoleRepositoryInterface::class => function (ContainerInterface $container): RoleRepository {
            return new RoleRepository(
                $container->get(MarkConnection::class)->getConnection(),
                $container->get(EntityMetadataFactory::class),
                $container->get(EntityHydrator::class),
            );
        },

        PermissionRepositoryInterface::class => function (ContainerInterface $container): PermissionRepository {
            return new PermissionRepository(
                $container->get(MarkConnection::class)->getConnection(),
                $container->get(EntityMetadataFactory::class),
                $container->get(EntityHydrator::class),
            );
        },

        PermissionRegistryInterface::class => PermissionRegistry::class,
        UserProviderInterface::class => function (ContainerInterface $container): UserProviderInterface {
            /** @var MarkRepositoryInterface $userRepository */
            $userRepository = $container->get(MarkRepositoryInterface::class);
            /** @var RoleRepositoryInterface $roleRepository */
            $roleRepository = $container->get(RoleRepositoryInterface::class);
            /** @var PasswordHasherInterface $passwordHasher */
            $passwordHasher = $container->get(PasswordHasherInterface::class);

            return new MarkProvider(
                userRepository: $userRepository,
                roleRepository: $roleRepository,
                passwordHasher: $passwordHasher,
            );
        },
    ],
    'middleware' => [
        \Marko\Session\Middleware\SessionMiddleware::class,
    ],
];

<?php

declare(strict_types=1);

use Marko\Mark\MarkProvider;
use Marko\Mark\Config\MarkConfig;
use Marko\Mark\Config\MarkConfigInterface;
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

return [
    'bindings' => [
        MarkConfigInterface::class => MarkConfig::class,
        MarkRepositoryInterface::class => MarkRepository::class,
        RoleRepositoryInterface::class => RoleRepository::class,
        PermissionRepositoryInterface::class => PermissionRepository::class,
        PermissionRegistryInterface::class => PermissionRegistry::class,
        UserProviderInterface::class => function (ContainerInterface $container): UserProviderInterface {
            return new MarkProvider(
                userRepository: $container->get(MarkRepositoryInterface::class),
                roleRepository: $container->get(RoleRepositoryInterface::class),
                passwordHasher: $container->get(PasswordHasherInterface::class),
            );
        },
    ],
];

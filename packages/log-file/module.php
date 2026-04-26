<?php

declare(strict_types=1);

use Marko\Core\Container\ContainerInterface;
use Marko\Log\Contracts\LoggerInterface;
use Marko\Log\File\Factory\FileLoggerFactory;
use Marko\Log\File\Rotation\DailyRotation;
use Marko\Log\File\Rotation\RotationInterface;

return [
    'bindings' => [
        RotationInterface::class => DailyRotation::class,
        LoggerInterface::class => function (ContainerInterface $container): LoggerInterface {
            return $container->get(FileLoggerFactory::class)($container);
        },
    ],
];

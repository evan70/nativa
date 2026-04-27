<?php

declare(strict_types=1);

namespace Marko\Log\File\Factory;

use Marko\Core\Container\ContainerInterface;
use Marko\Log\Config\LogConfig;
use Marko\Log\Contracts\LogFormatterInterface;
use Marko\Log\File\FileLogger;
use Marko\Log\File\Rotation\RotationInterface;

readonly class FileLoggerFactory
{
    public function __invoke(
        ContainerInterface $container,
    ): FileLogger {
        return new FileLogger(
            config: $container->get(LogConfig::class),
            formatter: $container->get(LogFormatterInterface::class),
            rotation: $container->get(RotationInterface::class),
        );
    }
}

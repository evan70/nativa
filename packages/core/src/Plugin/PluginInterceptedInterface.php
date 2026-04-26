<?php

declare(strict_types=1);

namespace Marko\Core\Plugin;

use Marko\Core\Container\ContainerInterface;

interface PluginInterceptedInterface
{
    /**
     * Initialize the interception state.
     */
    public function initInterception(
        object $pluginTarget,
        string $pluginTargetClass,
        ContainerInterface $pluginContainer,
        PluginRegistry $pluginRegistry,
    ): void;

    /**
     * Get the underlying target instance that this interceptor wraps.
     */
    public function getPluginTarget(): object;
}

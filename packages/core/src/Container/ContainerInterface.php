<?php

declare(strict_types=1);

namespace Marko\Core\Container;

use Closure;
use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ContainerInterface extends PsrContainerInterface
{
    /**
     * Register a class as a singleton (shared instance).
     */
    public function singleton(string $id): void;

    /**
     * Register a pre-built instance for an interface or class.
     */
    public function instance(
        string $id,
        object $instance,
    ): void;

    /**
     * Invoke a callable with auto-resolved dependencies.
     */
    public function call(Closure $callable): mixed;

    /**
     * Unbind an interface/class (remove binding, keep existing instance).
     *
     * @return bool True if binding was removed
     */
    public function unbind(string $id): bool;

    /**
     * Unbind a singleton (remove instance and shared flag).
     *
     * @return bool True if singleton was removed
     */
    public function unbindSingleton(string $id): bool;

    /**
     * Get all registered bindings.
     *
     * @return array<string, string|Closure>
     */
    public function getBindings(): array;

    /**
     * Get all registered singletons.
     *
     * @return array<string, bool>
     */
    public function getSingletons(): array;
}

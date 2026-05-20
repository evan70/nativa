<?php

declare(strict_types=1);

namespace App\Init\Container;

use Marko\Core\Container\Container as BaseContainer;
use ReflectionClass;

class Container extends BaseContainer
{
    public function unbind(string $interface): bool
    {
        $ref = new ReflectionClass(BaseContainer::class);
        $prop = $ref->getProperty('bindings');
        /** @var array<string, mixed> $bindings */
        $bindings = $prop->getValue($this);
        
        if (isset($bindings[$interface])) {
            unset($bindings[$interface]);
            $prop->setValue($this, $bindings);
            return true;
        }
        
        return false;
    }

    public function unbindSingleton(string $interface): bool
    {
        $ref = new ReflectionClass(BaseContainer::class);
        
        // Remove from shared
        $sharedProp = $ref->getProperty('shared');
        /** @var array<string, mixed> $shared */
        $shared = $sharedProp->getValue($this);
        $removedFromShared = false;
        if (isset($shared[$interface])) {
            unset($shared[$interface]);
            $sharedProp->setValue($this, $shared);
            $removedFromShared = true;
        }
        
        // Remove from instances
        $instancesProp = $ref->getProperty('instances');
        /** @var array<string, mixed> $instances */
        $instances = $instancesProp->getValue($this);
        $removedFromInstances = false;
        if (isset($instances[$interface])) {
            unset($instances[$interface]);
            $instancesProp->setValue($this, $instances);
            $removedFromInstances = true;
        }
        
        return $removedFromShared || $removedFromInstances;
    }

    /**
     * @return array<string, mixed>
     */
    public function getBindings(): array
    {
        $ref = new ReflectionClass(BaseContainer::class);
        $prop = $ref->getProperty('bindings');
        /** @var array<string, mixed> $bindings */
        $bindings = $prop->getValue($this);
        return $bindings;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSingletons(): array
    {
        $ref = new ReflectionClass(BaseContainer::class);
        $prop = $ref->getProperty('shared');
        /** @var array<string, mixed> $singletons */
        $singletons = $prop->getValue($this);
        return $singletons;
    }
}

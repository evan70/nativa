<?php

declare(strict_types=1);

namespace App\Init\Container;

use Marko\Core\Container\ContainerInterface as BaseContainerInterface;

interface ContainerInterface extends BaseContainerInterface
{
    public function unbind(string $interface): bool;
    
    public function unbindSingleton(string $interface): bool;
    
    /** @return array<string, mixed> */
    public function getBindings(): array;
    
    /** @return array<string, mixed> */
    public function getSingletons(): array;
}

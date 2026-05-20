<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Init\Module\ModuleGroupManagerInterface;
use Marko\Core\Attributes\Command;
use Marko\Core\Command\CommandInterface;
use Marko\Core\Command\Input;
use Marko\Core\Command\Output;
use Marko\Core\Container\Container;
use Marko\Core\Container\ContainerInterface;
use ReflectionClass;

#[Command(name: 'module:bindings', description: 'Show module bindings and groups')]
readonly class ModuleBindingsCommand implements CommandInterface
{
    public function __construct(
        private ContainerInterface $container,
    ) {}

    public function execute(Input $input, Output $output): int
    {
        // Use reflection to read private properties from the real app container.
        // The Application registers Marko\Core\Container\ContainerInterface as an instance,
        // so $this->container is the actual populated container — not a fresh autowired one.
        $ref = new ReflectionClass(Container::class);

        $bindingsProp = $ref->getProperty('bindings');
        $bindingsProp->setAccessible(true);
        /** @var array<string, string|\Closure> $bindings */
        $bindings = $bindingsProp->getValue($this->container);

        $sharedProp = $ref->getProperty('shared');
        $sharedProp->setAccessible(true);
        /** @var array<string, bool> $singletons */
        $singletons = $sharedProp->getValue($this->container);

        $output->writeLine('=== Container Bindings ===');
        $output->writeLine('Total: ' . count($bindings));
        $output->writeLine('');

        foreach ($bindings as $interface => $implementation) {
            $implStr = is_string($implementation) ? $implementation : 'closure';
            $isSingleton = isset($singletons[$interface]) ? ' [singleton]' : '';
            $output->writeLine("  $interface => $implStr$isSingleton");
        }

        // Get module groups
        if ($this->container->has(ModuleGroupManagerInterface::class)) {
            /** @var ModuleGroupManagerInterface $manager */
            $manager = $this->container->get(ModuleGroupManagerInterface::class);
            $groups = $manager->getGroups();

            $output->writeLine('');
            $output->writeLine('=== Module Groups ===');
            $output->writeLine('Total: ' . count($groups));
            $output->writeLine('');

            foreach ($groups as $name => $group) {
                $isCore = $group->isCore ? ' [core]' : '';
                $isActive = $manager->isGroupActive($name) ? ' [active]' : '';
                $timeout = $group->idleTimeout !== null ? " ({$group->idleTimeout})" : '';
                $output->writeLine("  $name$isCore$isActive$timeout");
                $output->writeLine("    module: {$group->moduleName}");
                if ($group->routes !== []) {
                    $output->writeLine('    routes: ' . implode(', ', $group->routes));
                }
                $output->writeLine('');
            }
        }

        return 0;
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers;

use Marko\Core\Attributes\Command;
use Marko\Core\Command\CommandInterface;
use Marko\Core\Command\Input;
use Marko\Core\Command\Output;
use App\Init\Module\ModuleGroupManagerInterface;

#[Command(name: 'module:bindings', description: 'Show module bindings and groups')]
readonly class ModuleBindingsCommand implements CommandInterface
{
    public function __construct(
        private \Marko\Core\Container\ContainerInterface $container,
    ) {}

    public function execute(Input $input, Output $output): int
    {
        // Get all bindings
        $bindings = $this->container->getBindings();
        $singletons = $this->container->getSingletons();

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
            $manager = $this->container->get(ModuleGroupManagerInterface::class);
            $groups = $manager->getGroups();

            $output->writeLine('');
            $output->writeLine('=== Module Groups ===');
            $output->writeLine('Total: ' . count($groups));
            $output->writeLine('');

            foreach ($groups as $name => $group) {
                $isCore = $group->isCore ? ' [core]' : '';
                $isActive = $manager->isGroupActive($name) ? ' [active]' : '';
                $timeout = $group->idleTimeout ? " ({$group->idleTimeout})" : '';
                $output->writeLine("  $name$isCore$isActive$timeout");
                $output->writeLine("    module: {$group->moduleName}");
                if ($group->routes) {
                    $output->writeLine('    routes: ' . implode(', ', $group->routes));
                }
                $output->writeLine('');
            }
        }

        return 0;
    }
}
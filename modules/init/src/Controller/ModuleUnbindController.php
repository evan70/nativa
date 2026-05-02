<?php

declare(strict_types=1);

namespace App\Controllers;

use Marko\Core\Attributes\Command;
use Marko\Core\Command\CommandInterface;
use Marko\Core\Command\Input;
use Marko\Core\Command\Output;
use App\Init\Module\ModuleGroupManagerInterface;

#[Command(name: 'module:unbind', description: 'Unbind a module group (for testing idle eviction)')]
readonly class ModuleUnbindCommand implements CommandInterface
{
    public function __construct(
        private \Marko\Core\Container\ContainerInterface $container,
    ) {}

    public function execute(Input $input, Output $output): int
    {
        $allArgs = $input->getArguments();
        
        // Find first arg - that's the group name
        // getArguments() returns [0] = first arg (admin), [1] = second arg
        $groupName = $allArgs[0] ?? null;

        if (!$groupName) {
            $output->writeLine('Error: Group name required');
            $output->writeLine('Usage: module:unbind <group-name>');
            $output->writeLine('');
            $output->writeLine('Available groups:');

            if ($this->container->has(ModuleGroupManagerInterface::class)) {
                $manager = $this->container->get(ModuleGroupManagerInterface::class);
                foreach ($manager->getGroups() as $name => $group) {
                    $isCore = $group->isCore ? ' [core]' : '';
                    $isActive = $manager->isGroupActive($name) ? ' [active]' : '';
                    $output->writeLine("  $name$isCore$isActive");
                }
            }

            return 1;
        }

        // Check if module group manager exists
        if (!$this->container->has(ModuleGroupManagerInterface::class)) {
            $output->writeLine('Error: ModuleGroupManager not available');
            return 1;
        }

        $manager = $this->container->get(ModuleGroupManagerInterface::class);

        // Check if group exists
        $group = $manager->getGroup($groupName);
        if (!$group) {
            $output->writeLine("Error: Group '$groupName' not found");
            return 1;
        }

        // Check if it's a core group
        if ($group->isCore) {
            $output->writeLine("Error: Cannot unbind core group '$groupName'");
            return 1;
        }

        // Check if group is active
        if (!$manager->isGroupActive($groupName)) {
            $output->writeLine("Group '$groupName' is already inactive");
            return 0;
        }

        // Unbind the group
        $manager->deactivateGroup($groupName);

        $output->writeLine("OK: Unbound group '$groupName'");
        $output->writeLine("  module: {$group->moduleName}");
        $output->writeLine("  routes: " . ($group->routes ? implode(', ', $group->routes) : 'none'));

        return 0;
    }
}
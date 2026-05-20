<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Init\Container\Container;
use App\Init\Module\ModuleGroupManagerInterface;
use Marko\Core\Attributes\Command;
use Marko\Core\Command\CommandInterface;
use Marko\Core\Command\Input;
use Marko\Core\Command\Output;

#[Command(name: 'module:unbind', description: 'Unbind a module group')]
readonly class ModuleUnbindCommand implements CommandInterface
{
    public function __construct(
        private Container $container,
    ) {}

    public function execute(Input $input, Output $output): int
    {
        $allArgs = $input->getArguments();
        
        $groupName = $allArgs[0] ?? null;
        $force = in_array('--force', $allArgs);

        if (!$groupName) {
            $output->writeLine('Error: Group name required');
            $output->writeLine('Usage: module:unbind <group-name> [--force]');
            $output->writeLine('');
            $output->writeLine('Available groups:');

            if ($this->container->has(ModuleGroupManagerInterface::class)) {
                /** @var ModuleGroupManagerInterface $manager */
                $manager = $this->container->get(ModuleGroupManagerInterface::class);
                foreach ($manager->getGroups() as $name => $group) {
                    $isCore = $group->isCore ? ' [core]' : '';
                    $isActive = $manager->isGroupActive($name) ? ' [active]' : '';
                    $output->writeLine("  $name$isCore$isActive");
                }
            }

            return 1;
        }

        if (!$this->container->has(ModuleGroupManagerInterface::class)) {
            $output->writeLine('Error: ModuleGroupManager not available');
            return 1;
        }

        /** @var ModuleGroupManagerInterface $manager */
        $manager = $this->container->get(ModuleGroupManagerInterface::class);
        $group = $manager->getGroup($groupName);

        if (!$group) {
            $output->writeLine("Error: Group '$groupName' not found");
            return 1;
        }

        if ($group->isCore) {
            $output->writeLine("Error: Cannot unbind core group '$groupName'");
            return 1;
        }

        $isActive = $manager->isGroupActive($groupName);

        if (!$isActive && !$force) {
            $output->writeLine("Group '$groupName' is already inactive (use --force to remove)");
            return 1;
        }

        if ($force && !$isActive) {
            // Force remove from registry
            $manager->removeGroup($groupName);
            $output->writeLine("OK: Removed group '$groupName' from registry");
        } elseif ($force) {
            $manager->deactivateGroup($groupName);
            $manager->removeGroup($groupName);
            $output->writeLine("OK: Removed group '$groupName'");
        } else {
            $manager->deactivateGroup($groupName);
            $output->writeLine("OK: Unbound group '$groupName'");
        }

        $output->writeLine("  module: {$group->moduleName}");
        $output->writeLine("  routes: " . ($group->routes ? implode(', ', $group->routes) : 'none'));

        return 0;
    }
}
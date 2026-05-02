<?php

declare(strict_types=1);

namespace App\Controllers;

use Marko\Core\Attributes\Command;
use Marko\Core\Command\CommandInterface;
use Marko\Core\Command\Input;
use Marko\Core\Command\Output;
use App\Init\Module\ModuleGroupManagerInterface;

#[Command(name: 'module:activate', description: 'Activate a module group')]
readonly class ModuleActivateCommand implements CommandInterface
{
    public function __construct(
        private \Marko\Core\Container\ContainerInterface $container,
    ) {}

    public function execute(Input $input, Output $output): int
    {
        $allArgs = $input->getArguments();
        $groupName = $allArgs[0] ?? null;

        if (!$groupName) {
            $output->writeLine('Usage: module:activate <group-name>');
            $output->writeLine('');
            $output->writeLine('Available groups:');
            
            if ($this->container->has(ModuleGroupManagerInterface::class)) {
                $manager = $this->container->get(ModuleGroupManagerInterface::class);
                foreach ($manager->getGroups() as $name => $group) {
                    if ($group->routes) {
                        $isActive = $manager->isGroupActive($name) ? ' [active]' : '';
                        $output->writeLine("  $name$isActive");
                    }
                }
            }
            return 1;
        }

        if (!$this->container->has(ModuleGroupManagerInterface::class)) {
            $output->writeLine('Error: ModuleGroupManager not available');
            return 1;
        }

        $manager = $this->container->get(ModuleGroupManagerInterface::class);
        $group = $manager->getGroup($groupName);

        if (!$group) {
            $output->writeLine("Error: Group '$groupName' not found");
            return 1;
        }

        if ($manager->isGroupActive($groupName)) {
            $output->writeLine("Group '$groupName' is already active");
            return 0;
        }

        $manager->activateGroup($groupName);
        
        $output->writeLine("OK: Activated group '$groupName'");
        $output->writeLine("  routes: " . ($group->routes ? implode(', ', $group->routes) : 'none'));

        return 0;
    }
}
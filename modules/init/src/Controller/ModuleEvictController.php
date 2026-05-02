<?php

declare(strict_types=1);

namespace App\Controllers;

use Marko\Core\Attributes\Command;
use Marko\Core\Command\CommandInterface;
use Marko\Core\Command\Input;
use Marko\Core\Command\Output;
use App\Init\Module\ModuleGroupManagerInterface;

#[Command(name: 'module:evict', description: 'Evict idle module groups')]
readonly class ModuleEvictCommand implements CommandInterface
{
    public function __construct(
        private \Marko\Core\Container\ContainerInterface $container,
    ) {}

    public function execute(Input $input, Output $output): int
    {
        $allArgs = $input->getArguments();
        $groupName = $allArgs[0] ?? null;

        if (!$this->container->has(ModuleGroupManagerInterface::class)) {
            $output->writeLine('Error: ModuleGroupManager not available');
            return 1;
        }

        $manager = $this->container->get(ModuleGroupManagerInterface::class);
        $groups = $manager->getGroups();

        if ($groupName) {
            // Evict specific group
            $group = $manager->getGroup($groupName);
            if (!$group) {
                $output->writeLine("Error: Group '$groupName' not found");
                return 1;
            }

            $timeout = $group->idleTimeout ?? '5m';
            $evicted = $manager->evictIfIdle($groupName, $timeout);
            
            if ($evicted) {
                $output->writeLine("OK: Evicted idle group '$groupName'");
            } else {
                $output->writeLine("Group '$groupName' is not idle (last used: {$group->lastUsed->format('Y-m-d H:i:s')})");
            }
            return 0;
        }

        // Evict all idle groups
        $output->writeLine('=== Evicting Idle Groups ===');
        
        $evicted = [];
        foreach ($groups as $name => $group) {
            if ($group->isCore) continue;
            if (!$manager->isGroupActive($name)) continue;
            
            $timeout = $group->idleTimeout ?? '5m';
            if ($manager->evictIfIdle($name, $timeout)) {
                $evicted[] = $name;
            }
        }

        if ($evicted) {
            $output->writeLine('Evicted: ' . implode(', ', $evicted));
        } else {
            $output->writeLine('No idle groups to evict');
        }

        return 0;
    }
}
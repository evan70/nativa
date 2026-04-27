<?php

declare(strict_types=1);

namespace Marko\DevServer\Command;

use Marko\Core\Attributes\Command;
use Marko\Core\Command\CommandInterface;
use Marko\Core\Command\Input;
use Marko\Core\Command\Output;
use Marko\Core\Path\ProjectPaths;
use Marko\DevServer\Process\PidFile;

#[Command(name: 'dev:status', description: 'Show development environment status', aliases: ['status'])]
class DevStatusCommand implements CommandInterface
{
    public function __construct(
        private readonly ProjectPaths $projectPaths,
    ) {}

    public function execute(Input $input, Output $output): int
    {
        $projectRoot = $this->projectPaths->base;
        $pidFile = new PidFile($projectRoot);
        $entries = $pidFile->read();

        if (empty($entries)) {
            $output->writeLine("No services are running.");
            return 0;
        }

        $output->writeLine(sprintf("%-15s %-10s %-10s %-10s %-20s", "Name", "PID", "Status", "Port", "Started At"));
        $output->writeLine(str_repeat("-", 70));

        foreach ($entries as $entry) {
            $isRunning = $pidFile->isRunning($entry->pid);
            $status = $isRunning ? "Running" : "Stopped";
            $output->writeLine(sprintf(
                "%-15s %-10d %-10s %-10d %-20s",
                $entry->name,
                $entry->pid,
                $status,
                $entry->port,
                $entry->startedAt
            ));
        }

        return 0;
    }
}

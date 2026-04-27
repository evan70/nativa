<?php

declare(strict_types=1);

namespace Marko\DevServer\Command;

use Marko\Core\Attributes\Command;
use Marko\Core\Command\CommandInterface;
use Marko\Core\Command\Input;
use Marko\Core\Command\Output;
use Marko\Core\Path\ProjectPaths;
use Marko\DevServer\Process\PidFile;
use Marko\DevServer\Process\ProcessManager;
use Marko\DevServer\Detection\DockerDetector;

#[Command(name: 'dev:down', description: 'Stop the development environment', aliases: ['down'])]
class DevDownCommand implements CommandInterface
{
    public function __construct(
        private readonly ProjectPaths $projectPaths,
    ) {}

    public function execute(Input $input, Output $output): int
    {
        $projectRoot = $this->projectPaths->base;
        $pidFile = new PidFile($projectRoot);
        $processManager = new ProcessManager($pidFile);

        $output->writeLine("Stopping all development services...");

        // Stop Docker if detected
        $dockerDetector = new DockerDetector($projectRoot);
        $commands = $dockerDetector->detect();
        if ($commands) {
            $output->writeLine("Stopping Docker: {$commands['downCommand']}");
            shell_exec($commands['downCommand']);
        }

        $processManager->stopAll();
        
        $output->writeLine("All services stopped.");

        return 0;
    }
}

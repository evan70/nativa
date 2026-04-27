<?php

declare(strict_types=1);

namespace Marko\DevServer\Command;

use Marko\Core\Attributes\Command;
use Marko\Core\Command\CommandInterface;
use Marko\Core\Command\Input;
use Marko\Core\Command\Output;
use Marko\Core\Path\ProjectPaths;
use Marko\DevServer\Process\PidFile;

#[Command(name: 'dev:open', description: 'Open the running development server in a browser', aliases: ['open'])]
class DevOpenCommand implements CommandInterface
{
    public function __construct(
        private readonly ProjectPaths $projectPaths,
    ) {}

    public function execute(Input $input, Output $output): int
    {
        $projectRoot = $this->projectPaths->base;
        $pidFile = new PidFile($projectRoot);
        $entries = $pidFile->read();

        $phpEntry = null;
        foreach ($entries as $entry) {
            if ($entry->name === 'php' && $pidFile->isRunning($entry->pid)) {
                $phpEntry = $entry;
                break;
            }
        }

        if (!$phpEntry) {
            $output->writeLine("Error: PHP development server is not running. Start it with 'marko up'.");
            return 1;
        }

        $url = "http://localhost:{$phpEntry->port}";
        $output->writeLine("Opening {$url} in your browser...");

        if (PHP_OS_FAMILY === 'Darwin') {
            exec("open {$url}");
        } elseif (PHP_OS_FAMILY === 'Windows') {
            exec("start {$url}");
        } else {
            // Check if xdg-open exists
            $hasXdgOpen = shell_exec('which xdg-open');
            if ($hasXdgOpen) {
                exec("xdg-open {$url}");
            } else {
                $output->writeLine("Please open {$url} in your browser.");
            }
        }

        return 0;
    }
}

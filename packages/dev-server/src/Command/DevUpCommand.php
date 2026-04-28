<?php

declare(strict_types=1);

namespace Marko\DevServer\Command;

use Marko\Core\Attributes\Command;
use Marko\Core\Command\CommandInterface;
use Marko\Core\Command\Input;
use Marko\Core\Command\Output;
use Marko\Core\Path\ProjectPaths;
use Marko\DevServer\Detection\DockerDetector;
use Marko\DevServer\Detection\FrontendDetector;
use Marko\DevServer\Exceptions\DevServerException;
use Marko\DevServer\Process\PidFile;
use Marko\DevServer\Process\ProcessManager;

#[Command(name: 'dev:up', description: 'Start the development environment', aliases: ['up'])]
class DevUpCommand implements CommandInterface
{
    public function __construct(
        private readonly ProjectPaths $projectPaths,
    ) {}

    public function execute(Input $input, Output $output): int
    {
        $projectRoot = $this->projectPaths->base;
        $config = $this->loadConfig($projectRoot);

        $portValue = $input->getOption('port') ?? $input->getOption('p') ?? $config['port'] ?? 8000;
        $port = (int) $portValue;
        
        $detach = ($input->hasOption('detach') || $input->hasOption('d')) 
            || (!($input->hasOption('foreground') || $input->hasOption('f')) && ($config['detach'] ?? true));

        if ($this->isPortInUse($port)) {
            throw DevServerException::portInUse($port);
        }

        if (!file_exists($projectRoot . '/public/index.php')) {
            throw DevServerException::missingEntryPoint();
        }

        $pidFile = new PidFile($projectRoot);
        $processManager = new ProcessManager($pidFile, $port);

        $output->writeLine("Starting development environment on http://localhost:{$port}");

        // 1. Docker
        $dockerOption = $config['docker'] ?? true;
        if ($dockerOption !== false) {
            $dockerDetector = new DockerDetector($projectRoot);
            $detected = $dockerDetector->detect();
            if ($detected || is_string($dockerOption)) {
                $cmd = is_string($dockerOption) ? $dockerOption : $detected['upCommand'];
                $output->writeLine("Starting Docker: {$cmd}");
                $processManager->start('docker', $cmd, $detach);
            }
        }

        // 2. PHP Server - use public/index.php as router for core Router
        // This serves static files directly and bootstraps the core app for dynamic requests
        $indexPath = $projectRoot . '/public/index.php';
        $phpCommand = sprintf('php -S localhost:%d -t public %s', $port, escapeshellarg($indexPath));
        $output->writeLine("Starting PHP server: {$phpCommand}");
        $processManager->start('php', $phpCommand, $detach);

        // 3. Frontend
        $frontendOption = $config['frontend'] ?? true;
        if ($frontendOption !== false) {
            $frontendDetector = new FrontendDetector($projectRoot);
            $detectedCmd = $frontendDetector->detect();
            if ($detectedCmd || is_string($frontendOption)) {
                $cmd = is_string($frontendOption) ? $frontendOption : $detectedCmd;
                $output->writeLine("Starting Frontend: {$cmd}");
                $processManager->start('frontend', $cmd, $detach);
            }
        }

        // 4. PubSub
        $pubsubOption = $config['pubsub'] ?? true;
        if ($pubsubOption !== false) {
            $cmd = is_string($pubsubOption) ? $pubsubOption : 'php marko pubsub:listen';
            try {
                 $processManager->start('pubsub', $cmd, $detach);
                 $output->writeLine("Starting PubSub: {$cmd}");
            } catch (\Exception $e) {
                // Skip if it fails (might not be implemented in the app)
            }
        }

        // 5. Custom processes
        $customProcesses = $config['processes'] ?? [];
        foreach ($customProcesses as $name => $cmd) {
            $output->writeLine("Starting {$name}: {$cmd}");
            $processManager->start($name, $cmd, $detach);
        }

        if (!$detach) {
            $output->writeLine("Running in foreground. Press Ctrl+C to stop.");
            
            if (function_exists('pcntl_signal')) {
                pcntl_async_signals(true);
                pcntl_signal(SIGINT, function () use ($processManager, $output) {
                    $output->writeLine("\nStopping services...");
                    $processManager->stopAll();
                    exit(0);
                });
            }

            $processManager->runForeground();
        } else {
            $output->writeLine("Services started in background. Use 'marko status' to check and 'marko down' to stop.");
        }

        return 0;
    }

    private function loadConfig(string $projectRoot): array
    {
        $configFile = $projectRoot . '/config/dev.php';
        if (file_exists($configFile)) {
            return require $configFile;
        }
        return [];
    }

    private function isPortInUse(int $port): bool
    {
        $connection = @fsockopen('localhost', $port, $errno, $errstr, 1);
        if (is_resource($connection)) {
            fclose($connection);
            return true;
        }
        return false;
    }
}

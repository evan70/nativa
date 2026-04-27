<?php

declare(strict_types=1);

namespace Marko\Cli;

use Closure;
use Marko\Cli\Exceptions\CliException;
use Marko\Cli\Exceptions\ProjectNotFoundException;
use Marko\Core\Application;
use Marko\Core\Command\Input;
use Marko\Core\Command\Output;
use Marko\Core\Path\ProjectPaths;
use Throwable;

readonly class CliKernel
{
    /** @var Closure(string): object */
    private Closure $applicationFactory;

    private Output $output;

    /**
     * @param Closure(string): object|null $applicationFactory
     */
    public function __construct(
        private ProjectFinder $projectFinder,
        ?Closure $applicationFactory = null,
        ?Output $output = null,
    ) {
        $this->applicationFactory = $applicationFactory ?? fn (string $projectRoot): Application => new Application(
            vendorPath: ProjectPaths::resolvePackagesRoot($projectRoot),
            modulesPath: $projectRoot . '/modules',
            appPath: $projectRoot . '/app',
            basePath: $projectRoot,
        );
        $this->output = $output ?? new Output();
    }

    /**
     * @param array<int, string> $argv
     *
     * @throws ProjectNotFoundException
     */
    public function run(
        array $argv,
    ): int {
        try {
            return $this->doRun($argv);
        } catch (Throwable $e) {
            $this->displayException($e);

            return 1;
        }
    }

    /**
     * @param array<int, string> $argv
     *
     * @throws ProjectNotFoundException
     */
    private function doRun(
        array $argv,
    ): int {
        $projectRoot = $this->projectFinder->find();

        if ($projectRoot === null) {
            throw ProjectNotFoundException::fromDirectory(getcwd() ?: '.');
        }

        // Load the project's autoloader
        $bootstrapAutoload = $projectRoot . '/bootstrap/autoload.php';
        $composerAutoload = $projectRoot . '/vendor/autoload.php';

        if (is_file($bootstrapAutoload)) {
            require_once $bootstrapAutoload;
        } elseif (is_file($composerAutoload)) {
            require_once $composerAutoload;
        }

        // Create and boot the application
        $app = ($this->applicationFactory)($projectRoot);
        $app->initialize();

        // Parse input and create output
        $input = new Input($argv);

        // Get command name (default to 'list' if none provided)
        $commandName = $input->getCommand() ?? 'list';

        // Delegate to command runner
        return $app->commandRunner->run($commandName, $input, $this->output);
    }

    private function displayException(
        Throwable $e,
    ): void {
        $this->output->writeLine('');
        $this->output->writeLine("Error: {$e->getMessage()}");

        if (method_exists($e, 'getContext') && $e->getContext() !== '') {
            $this->output->writeLine("  Context: {$e->getContext()}");
        }

        if (method_exists($e, 'getSuggestion') && $e->getSuggestion() !== '') {
            $this->output->writeLine("  Suggestion: {$e->getSuggestion()}");
        }

        $this->output->writeLine('');
    }
}

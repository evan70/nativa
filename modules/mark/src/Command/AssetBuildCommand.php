<?php

declare(strict_types=1);

namespace Marko\Mark\Command;

use Marko\Core\Attributes\Command;
use Marko\Core\Command\CommandInterface;
use Marko\Core\Command\Input;
use Marko\Core\Command\Output;
use Marko\Core\Path\ProjectPaths;

#[Command(name: 'asset:build', description: 'Build frontend assets using pnpm and vite')]
readonly class AssetBuildCommand implements CommandInterface
{
    public function __construct(
        private ProjectPaths $projectPaths,
    ) {}

    public function execute(
        Input $input,
        Output $output,
    ): int {
        $template = $input->getArgument(0);
        $assetDir = $this->projectPaths->base . '/templates' . ($template ? '/' . $template : '');

        if (!is_dir($assetDir)) {
            $output->writeLine("Asset directory not found: $assetDir");
            return 1;
        }

        $output->writeLine("Building frontend assets in $assetDir...");

        passthru("cd " . escapeshellarg($assetDir) . " && pnpm install && pnpm build", $exitCode);

        if ($exitCode !== 0) {
            $output->writeLine("Asset build failed with exit code $exitCode");
            return $exitCode;
        }

        $output->writeLine("Assets built successfully!");

        return 0;
    }
}

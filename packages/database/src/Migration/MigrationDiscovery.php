<?php

declare(strict_types=1);

namespace Marko\Database\Migration;

use Marko\Core\Path\ProjectPaths;

/**
 * Discovers schema migrations across vendor, modules, app, and main database directories.
 */
readonly class MigrationDiscovery
{
    private string $vendorPath;
    private string $modulesPath;
    private string $appPath;
    private string $databasePath;
    private string $packagesPath;

    public function __construct(
        ProjectPaths $paths,
    ) {
        $this->vendorPath = $paths->vendor;
        $this->modulesPath = $paths->modules;
        $this->appPath = $paths->app;
        $this->databasePath = $paths->database;
        $this->packagesPath = $paths->base . '/packages';
    }

    /**
     * Discover all migrations from all sources.
     *
     * @return array<string, string> Map of migration name to full path
     */
    public function discover(): array
    {
        $migrations = [];

        // Discover from database/migrations/
        $migrations = array_merge($migrations, $this->discoverFromPath($this->databasePath . '/migrations'));

        // Discover from vendor/*/*/database/migrations/
        $migrations = array_merge($migrations, $this->discoverFromPatterns([
            $this->vendorPath . '/*/*/database/migrations/*.php',
            $this->vendorPath . '/*/database/migrations/*.php',
        ]));

        // Discover from modules/*/database/migrations/
        $migrations = array_merge($migrations, $this->discoverFromPatterns([
            $this->modulesPath . '/*/database/migrations/*.php',
            $this->modulesPath . '/*/*/database/migrations/*.php',
        ]));

        // Discover from app/*/database/migrations/
        $migrations = array_merge($migrations, $this->discoverFromPatterns([
            $this->appPath . '/database/migrations/*.php',
            $this->appPath . '/*/database/migrations/*.php',
        ]));

        // Discover from packages/*/database/migrations/
        $migrations = array_merge($migrations, $this->discoverFromPatterns([
            $this->packagesPath . '/*/database/migrations/*.php',
        ]));

        return $migrations;
    }

    /**
     * Discover migrations from a path.
     *
     * @return array<string, string>
     */
    private function discoverFromPath(string $path): array
    {
        if (!is_dir($path)) {
            return [];
        }

        $files = glob($path . '/*.php');

        if ($files === false) {
            return [];
        }

        $migrations = [];
        foreach ($files as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            $migrations[$name] = $file;
        }

        return $migrations;
    }

    /**
     * @param array<string> $patterns
     * @return array<string, string>
     */
    private function discoverFromPatterns(array $patterns): array
    {
        $migrations = [];

        foreach ($patterns as $pattern) {
            $files = glob($pattern);

            if ($files === false) {
                continue;
            }

            foreach ($files as $file) {
                $name = pathinfo($file, PATHINFO_FILENAME);
                $migrations[$name] = $file;
            }
        }

        return $migrations;
    }
}

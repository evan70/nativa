<?php

declare(strict_types=1);

namespace Marko\Core\Path;

/**
 * Provides standard project directory paths.
 *
 * Centralizes path resolution so all framework components
 * use consistent paths. Defaults to getcwd() as base path,
 * which works for both CLI and web contexts.
 */
readonly class ProjectPaths
{
    public string $base;

    public string $vendor;

    public string $packages;

    public string $modules;

    public string $app;

    public string $config;

    public string $database;

    public function __construct(
        ?string $basePath = null,
    ) {
        $this->base = $basePath ?? getcwd();
        $this->packages = self::resolvePackagesRoot($this->base);
        $this->vendor = $this->packages;
        $this->modules = $this->base . '/modules';
        $this->app = $this->base . '/app';
        $this->config = $this->base . '/config';
        $this->database = $this->base . '/database';
    }

    public static function resolvePackagesRoot(
        string $basePath,
    ): string {
        $vendorRoot = $basePath . '/vendor';

        if (is_dir($vendorRoot . '/marko/core')) {
            return $vendorRoot;
        }

        $packagesRoot = $basePath . '/packages';

        if (is_dir($packagesRoot)) {
            return $packagesRoot;
        }

        return $vendorRoot;
    }
}

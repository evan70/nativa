<?php

declare(strict_types=1);

namespace Marko\Core\Module;

use Marko\Core\Exceptions\ModuleException;

/**
 * Discovers Marko modules in configured directories.
 *
 * A directory is a Marko module if it contains:
 * - composer.json with extra.marko.module: true (required - marks as Marko module)
 * - module.php (optional - provides Marko-specific config like bindings)
 *
 * Discovery depths vary by source:
 * - vendor: Two levels deep (vendor/vendor-name/package-name/)
 * - modules: Recursive at any depth
 * - app: One level deep (app/module-name/)
 */
readonly class ModuleDiscovery
{
    public function __construct(
        private ManifestParser $parser,
    ) {}

    /**
     * Discover modules in vendor directory.
     *
     * Supports both:
     * - Two levels deep: vendor/vendor-name/package-name/ (standard Composer)
     * - One level deep: packages/package-name/ (Marko monorepo style)
     *
     * @return array<ModuleManifest>
     * @throws ModuleException
     */
    public function discoverInVendor(
        string $vendorDir,
    ): array {
        $modules = [];

        if (!is_dir($vendorDir)) {
            return $modules;
        }

        foreach ($this->scanDirectory($vendorDir) as $entryName) {
            $entryPath = $vendorDir . '/' . $entryName;

            if (!is_dir($entryPath)) {
                continue;
            }

            // Check if this is a module directly (one level: packages/package-name/)
            if ($this->isMarkoModule($entryPath)) {
                $manifest = $this->parser->parse($entryPath);
                $modules[] = $this->withPathAndSource($manifest, $entryPath, 'vendor');
                continue;
            }

            // Otherwise scan one level deeper (two levels: vendor/vendor-name/package-name/)
            foreach ($this->scanDirectory($entryPath) as $packageName) {
                $packagePath = $entryPath . '/' . $packageName;

                if ($this->isMarkoModule($packagePath)) {
                    $manifest = $this->parser->parse($packagePath);
                    $modules[] = $this->withPathAndSource($manifest, $packagePath, 'vendor');
                }
            }
        }

        return $modules;
    }

    /**
     * Discover modules in modules directory (recursive, any depth)
     *
     * @return array<ModuleManifest>
     * @throws ModuleException
     */
    public function discoverInModules(
        string $modulesDir,
    ): array {
        $modules = [];

        if (!is_dir($modulesDir)) {
            return $modules;
        }

        $this->discoverRecursively($modulesDir, 'modules', $modules);

        return $modules;
    }

    /**
     * Discover modules in app directory (one level deep: app/module-name/)
     *
     * @return array<ModuleManifest>
     * @throws ModuleException
     */
    public function discoverInApp(
        string $appDir,
    ): array {
        $modules = [];

        if (!is_dir($appDir)) {
            return $modules;
        }

        if ($this->isMarkoModule($appDir)) {
            $manifest = $this->parser->parse($appDir);
            $modules[] = $this->withPathAndSource($manifest, $appDir, 'app');

            return $modules;
        }

        // Scan app/*/
        $moduleNames = $this->scanDirectory($appDir);

        foreach ($moduleNames as $moduleName) {
            $modulePath = $appDir . '/' . $moduleName;

            if (!is_dir($modulePath)) {
                continue;
            }

            if ($this->isMarkoModule($modulePath)) {
                $manifest = $this->parser->parse($modulePath);
                $modules[] = $this->withPathAndSource($manifest, $modulePath, 'app');
            } elseif (is_file($modulePath . '/composer.json')) {
                throw new ModuleException(
                    message: "Module '$moduleName' could not be loaded",
                    context: "Module '$moduleName' at $modulePath — composer.json missing \"extra.marko.module\" key",
                    suggestion: 'Add {"extra": {"marko": {"module": true}}} to the module\'s composer.json',
                );
            }
        }

        return $modules;
    }

    /**
     * Check if a directory is a Marko module.
     *
     * A package is a Marko module if and only if its composer.json contains
     * extra.marko.module: true. This follows the Laravel/Symfony pattern
     * for package auto-discovery.
     */
    private function isMarkoModule(
        string $path,
    ): bool {
        return $this->parser->isMarkoModule($path);
    }

    /**
     * Create a new manifest with path and source set.
     */
    private function withPathAndSource(
        ModuleManifest $manifest,
        string $path,
        string $source,
    ): ModuleManifest {
        return new ModuleManifest(
            name: $manifest->name,
            version: $manifest->version,
            enabled: $manifest->enabled,
            require: $manifest->require,
            after: $manifest->after,
            before: $manifest->before,
            bindings: $manifest->bindings,
            singletons: $manifest->singletons,
            path: $path,
            source: $source,
            autoload: $manifest->autoload,
            boot: $manifest->boot,
            group: $manifest->group,
            routes: $manifest->routes,
            idleTimeout: $manifest->idleTimeout,
            isCore: $manifest->isCore,
        );
    }

    /**
     * @param array<ModuleManifest> $modules
     * @throws ModuleException
     */
    private function discoverRecursively(
        string $dir,
        string $source,
        array &$modules,
    ): void {
        if ($this->isMarkoModule($dir)) {
            $manifest = $this->parser->parse($dir);
            $modules[] = $this->withPathAndSource($manifest, $dir, $source);

            return; // Don't recurse into module directories
        }

        if (is_file($dir . '/composer.json')) {
            $moduleName = basename($dir);
            throw new ModuleException(
                message: "Module '$moduleName' could not be loaded",
                context: "Module '$moduleName' at $dir — composer.json missing \"extra.marko.module\" key",
                suggestion: 'Add {"extra": {"marko": {"module": true}}} to the module\'s composer.json',
            );
        }

        // No module found, recurse into subdirectories
        $items = $this->scanDirectory($dir);

        foreach ($items as $item) {
            $itemPath = $dir . '/' . $item;

            if (is_dir($itemPath)) {
                $this->discoverRecursively($itemPath, $source, $modules);
            }
        }
    }

    /**
     * @return array<string>
     */
    private function scanDirectory(
        string $dir,
    ): array {
        $items = scandir($dir);

        if ($items === false) {
            return [];
        }

        return array_filter($items, fn (string $item) => $item !== '.' && $item !== '..');
    }
}

<?php

declare(strict_types=1);

namespace App\Init\Bootstrap;

/**
 * Path resolution helpers for Nativa application bootstrap.
 *
 * Provides the custom path logic that was previously patched into
 * packages/core — now properly living in a module.
 */
final class Paths
{
    /**
     * Resolve the root directory containing Marko packages.
     *
     * Checks for packages in priority order:
     * 1. vendor/ — if vendor/marko/core exists (symlink or actual install)
     * 2. packages/ — local framework source (dev, or dist after build.php)
     *
     * This allows the framework to work both with vendor-based Composer
     * installs and with the vendorless production artifact where packages/
     * replaces vendor/.
     *
     * In dev, "make install" and "make update" create vendor/marko -> ../packages
     * symlink so that discoverInVendor('vendor/') finds the two-level structure
     * (vendor/marko/core/) that the upstream ModuleDiscovery expects.
     */
    public static function resolvePackagesRoot(string $basePath): string
    {
        $vendorRoot = $basePath . '/vendor';

        // Check for vendor/marko/core (symlink or actual composer install)
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

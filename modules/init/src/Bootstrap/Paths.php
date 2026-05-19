<?php

declare(strict_types=1);

namespace App\Init\Bootstrap;

/**
 * Path resolution helpers for Nativa application bootstrap.
 *
 * Provides the custom path logic that was previously patched into
 * packages/core — now properly living in a module.
 *
 * ModuleDiscovery::discoverInVendor() supports both standard 2-level
 * nesting (vendor/vendor-name/package-name/) and flat structures
 * (vendor/package-name/), so resolvePackagesRoot can return either
 * vendor/ or packages/ and discovery works in both cases.
 */
final class Paths
{
    /**
     * Resolve the root directory containing Marko packages.
     *
     * Checks for packages in priority order:
     * 1. vendor/ — if vendor/marko/core exists (Composer install)
     * 2. packages/ — production artifact (flat structure)
     *
     * ModuleDiscovery::discoverInVendor() handles both the standard
     * 2-level nesting (vendor/vendor-name/package-name/) and the flat
     * production structure (packages/package-name/), so no bridging
     * directories or symlinks are needed.
     */
    public static function resolvePackagesRoot(string $basePath): string
    {
        $vendorRoot = $basePath . '/vendor';

        // Check for standard Composer vendor structure (2-level nesting)
        if (is_dir($vendorRoot . '/marko/core')) {
            return $vendorRoot;
        }

        $packagesRoot = $basePath . '/packages';

        if (is_dir($packagesRoot)) {
            // Flat structure — discoverInVendor() now handles this directly
            return $packagesRoot;
        }

        return $vendorRoot;
    }
}

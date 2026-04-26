#!/usr/bin/env php
<?php

/**
 * Build script for production deployment.
 *
 * This script prepares a vendorless production artifact that boots directly
 * from framework packages stored in /packages.
 *
 * Usage: php build.php
 */

echo "Starting Marko Framework Production Build...\n";

$rootDir = __DIR__;
$distDir = $rootDir . '/dist';

// 1. Clean previous build
if (is_dir($distDir)) {
    echo "Cleaning existing dist directory...\n";
    exec("rm -rf " . escapeshellarg($distDir));
}
mkdir($distDir, 0755, true);

// 1.5. Build frontend assets
echo "Building frontend assets...\n";
$assetDir = $rootDir . '/templates/admin-dashboard';
if (is_dir($assetDir)) {
    passthru("cd " . escapeshellarg($assetDir) . " && pnpm install && pnpm build", $exitCode);
    if ($exitCode !== 0) {
        echo "Warning: Frontend asset build failed. Proceeding with existing assets in public/mark if any.\n";
    }
}

// 2. Install dependencies locally (only for the build environment)
if (!is_dir($rootDir . '/vendor')) {
    echo "Installing dependencies...\n";
    passthru('composer install --no-dev --optimize-autoloader', $exitCode);
    if ($exitCode !== 0) {
        die("Composer install failed.\n");
    }
} else {
    echo "Dependencies already present.\n";
}

// 3. Copy necessary runtime files to dist
echo "Preparing production artifacts...\n";

// Copy source code directories
$sourceDirs = ['app', 'bootstrap', 'modules', 'packages', 'config', 'database', 'routes', 'public', 'storage'];
foreach ($sourceDirs as $dir) {
    if (is_dir($rootDir . '/' . $dir)) {
        echo "   Copying $dir...\n";
        // Use rsync or cp for speed, ignoring git files
        exec("cp -r " . escapeshellarg($rootDir . '/' . $dir) . " " . escapeshellarg($distDir . '/'));
        // Remove .gitkeep or test files if needed here
    }
}

copyVendorMarkoPackages($rootDir, $distDir);

if (is_file($rootDir . '/marko')) {
    echo "   Copying marko CLI binary...\n";
    copy($rootDir . '/marko', $distDir . '/marko');
    chmod($distDir . '/marko', 0755);
}

writeRuntimeManifest($distDir);
removeComposerFiles($distDir);

// Create a minimal .gitignore for dist
file_put_contents($distDir . '/.gitignore', "*\n!.gitignore\n");

echo "Build complete! Production ready files are in './dist'\n";
echo "The 'dist' folder contains:\n";
echo "   - Bootstrap autoloader resolving packages/ directly\n";
echo "   - Source code (app, modules, packages)\n";
echo "   - All required marko/* runtime packages moved into packages/\n";
echo "   - NO vendor directory\n";
echo "   - NO root composer.json / composer.lock\n";
echo "   - NO package composer.json files (runtime manifest generated)\n";
echo "\nDeploy the contents of './dist' to production.\n";

/**
 * Copy marko packages that still live only under vendor/marko into dist/packages.
 */
function copyVendorMarkoPackages(
    string $rootDir,
    string $distDir,
): void {
    $vendorMarkoDir = $rootDir . '/vendor/marko';
    $distPackagesDir = $distDir . '/packages';

    if (!is_dir($vendorMarkoDir) || !is_dir($distPackagesDir)) {
        return;
    }

    foreach (scandir($vendorMarkoDir) ?: [] as $packageName) {
        if ($packageName === '.' || $packageName === '..') {
            continue;
        }

        $sourcePath = $vendorMarkoDir . '/' . $packageName;
        $targetPath = $distPackagesDir . '/' . $packageName;

        if (!is_dir($sourcePath) || file_exists($targetPath)) {
            continue;
        }

        echo "   Copying vendor/marko/$packageName into packages/...\n";
        copyDirectory($sourcePath, $targetPath);
    }
}

/**
 * Generate a runtime manifest so production does not depend on composer.json files.
 */
function writeRuntimeManifest(
    string $distDir,
): void {
    $manifest = [];

    foreach (findFilesByName($distDir, 'composer.json') as $composerFile) {
        $contents = file_get_contents($composerFile);

        if ($contents === false) {
            continue;
        }

        $decoded = json_decode($contents, true);

        if (!is_array($decoded)) {
            continue;
        }

        $modulePath = dirname($composerFile);
        $relativePath = ltrim(str_replace(str_replace('\\', '/', $distDir), '', str_replace('\\', '/', $modulePath)), '/');
        $manifest[$relativePath] = $decoded;
    }

    ksort($manifest);

    $manifestFile = $distDir . '/bootstrap/runtime-manifest.php';
    $export = var_export($manifest, true);
    file_put_contents($manifestFile, "<?php\n\ndeclare(strict_types=1);\n\nreturn $export;\n");
}

/**
 * Remove composer metadata from dist after runtime-manifest.php has been generated.
 */
function removeComposerFiles(
    string $distDir,
): void {
    foreach (findFilesByName($distDir, 'composer.json') as $composerFile) {
        unlink($composerFile);
    }

    foreach (findFilesByName($distDir, 'composer.lock') as $composerLockFile) {
        unlink($composerLockFile);
    }
}

/**
 * @return array<string>
 */
function findFilesByName(
    string $directory,
    string $filename,
): array {
    if (!is_dir($directory)) {
        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getFilename() === $filename) {
            $files[] = $file->getPathname();
        }
    }

    return $files;
}

function copyDirectory(
    string $source,
    string $target,
): void {
    $resolvedSource = is_link($source) ? realpath($source) : $source;

    if ($resolvedSource === false || !is_dir($resolvedSource)) {
        return;
    }

    if (!is_dir($target)) {
        mkdir($target, 0755, true);
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($resolvedSource, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST,
    );

    foreach ($iterator as $item) {
        $destinationPath = $target . '/' . $iterator->getSubPathName();

        if ($item->isDir()) {
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            continue;
        }

        copy($item->getPathname(), $destinationPath);
    }
}

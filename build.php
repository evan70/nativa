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

echo "🚀 Starting Marko Framework Production Build...\n";

$rootDir = __DIR__;
$distDir = $rootDir . '/dist';

// 1. Clean previous build
if (is_dir($distDir)) {
    echo "🧹 Cleaning existing dist directory...\n";
    exec("rm -rf " . escapeshellarg($distDir));
}
mkdir($distDir, 0755, true);

// 2. Install dependencies locally (only for the build environment)
if (!is_dir($rootDir . '/vendor')) {
    echo "📦 Installing dependencies...\n";
    passthru('composer install --no-dev --optimize-autoloader', $exitCode);
    if ($exitCode !== 0) {
        die("❌ Composer install failed.\n");
    }
} else {
    echo "✅ Dependencies already present.\n";
}

// 3. Copy necessary runtime files to dist
echo "📂 Preparing production artifacts...\n";

// Copy source code directories
$sourceDirs = ['app', 'bootstrap', 'modules', 'packages', 'config', 'routes'];
foreach ($sourceDirs as $dir) {
    if (is_dir($rootDir . '/' . $dir)) {
        echo "   Copying $dir...\n";
        // Use rsync or cp for speed, ignoring git files
        exec("cp -r " . escapeshellarg($rootDir . '/' . $dir) . " " . escapeshellarg($distDir . '/'));
        // Remove .gitkeep or test files if needed here
    }
}

// Create a minimal .gitignore for dist
file_put_contents($distDir . '/.gitignore', "*\n!.gitignore\n");

echo "✅ Build complete! Production ready files are in './dist'\n";
echo "ℹ️  The 'dist' folder contains:\n";
echo "   - Bootstrap autoloader resolving packages/ directly\n";
echo "   - Source code (app, modules, packages)\n";
echo "   - NO vendor directory\n";
echo "   - NO root composer.json / composer.lock\n";
echo "   - Package composer.json manifests kept for runtime discovery\n";
echo "\n📦 Deploy the contents of './dist' to production.\n";

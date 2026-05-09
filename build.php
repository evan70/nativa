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

// 1.5. Build frontend assets (can be skipped when CI already built them)
$skipFrontendBuild = getenv('MARKO_SKIP_FRONTEND_BUILD') === '1';
$assetDir = $rootDir . '/templates';

if ($skipFrontendBuild) {
    echo "Skipping frontend asset build (MARKO_SKIP_FRONTEND_BUILD=1).\n";
} elseif (is_dir($assetDir)) {
    echo "Building frontend assets...\n";
    passthru("cd " . escapeshellarg($assetDir) . " && pnpm install --frozen-lockfile && pnpm build", $exitCode);
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
// Note: vendor is NOT included - production deploy runs: composer install --no-dev
$sourceDirs = ['app', 'bootstrap', 'modules', 'packages', 'config', 'database', 'routes', 'public', 'storage', 'templates'];
foreach ($sourceDirs as $dir) {
    if (is_dir($rootDir . '/' . $dir)) {
        echo "   Copying $dir...\n";
        // Exclude node_modules from templates
        if ($dir === 'templates') {
            exec("rsync -a --exclude='node_modules' " . escapeshellarg($rootDir . '/' . $dir) . "/ " . escapeshellarg($distDir . '/' . $dir . '/'));
        } else {
            exec("cp -r " . escapeshellarg($rootDir . '/' . $dir) . " " . escapeshellarg($distDir . '/'));
        }
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
replaceAutoloadForProduction($distDir);

// Copy .env.example as production .env template
if (is_file($rootDir . '/.env.example')) {
    copy($rootDir . '/.env.example', $distDir . '/.env');
    echo "   Copying .env.example as .env template\n";
}

// Create storage directories for production
$storageDirs = [
    'storage/data',
    'storage/framework/sessions',
    'storage/framework/cache',
    'storage/framework/views',
];
foreach ($storageDirs as $dir) {
    $path = $distDir . '/' . $dir;
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
        echo "   Creating $dir/\n";
    }
}

// Initialize database (run migrations and seed)
if (is_file($distDir . '/marko')) {
    echo "   Running database migrations...\n";
    chdir($distDir);
    passthru('php marko db:migrate --no-interaction --no-generate', $exitCode);
    echo "   Running database seeding...\n";
    passthru('php marko db:seed --no-interaction', $exitCode);
    chdir($rootDir);
}

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
echo "   - autoload.php updated for production (no vendor dependency)\n";
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

/**
 * Replace autoload.php with production version that doesn't depend on vendor.
 */
function replaceAutoloadForProduction(string $distDir): void
{
    $productionAutoload = <<<'PHP'
<?php

declare(strict_types=1);

// Production autoloader - no vendor dependency
$runtimeManifestFile = __DIR__ . '/runtime-manifest.php';
$runtimeManifest = is_file($runtimeManifestFile)
    ? require $runtimeManifestFile
    : [];

class MarkoAutoloader
{
    private array $classMap = [];
    private array $psr4Map = [];

    public function __construct(
        private string $packagesPath,
        private string $basePath,
        private array $runtimeManifest = [],
    ) {
        $this->additionalPaths = [$packagesPath];
    }

    public function addPath(string $path): void
    {
        if (is_dir($path)) {
            $this->additionalPaths[] = $path;
        }
    }

    private array $additionalPaths = [];

    public function register(): void
    {
        spl_autoload_register([$this, 'loadClass'], true, true);
        $this->registerFunctions();
    }

    private function registerFunctions(): void
    {
        $envPackagePath = $this->packagesPath . '/env';
        if (is_dir($envPackagePath)) {
            $functionsFile = $envPackagePath . '/src/functions.php';
            if (file_exists($functionsFile)) {
                require_once $functionsFile;
            }
        }
    }

    public function build(): void
    {
        $this->classMap = [];
        $this->psr4Map = [];
        foreach ($this->additionalPaths as $rootPath) {
            $this->scanPath($rootPath);
        }
    }

    private function scanPath(string $rootPath): void
    {
        $packages = glob($rootPath . '/*', GLOB_ONLYDIR);
        if (!$packages) {
            return;
        }
        foreach ($packages as $packageDir) {
            $composer = $this->packageMetadata($packageDir);
            if ($composer === null) {
                continue;
            }
            $this->registerPackage($packageDir, $composer);
            $this->loadModule($packageDir);
        }
    }

    private function packageMetadata(string $packageDir): ?array
    {
        $relativePath = $this->relativePath($packageDir);
        if ($relativePath !== null && isset($this->runtimeManifest[$relativePath])) {
            $metadata = $this->runtimeManifest[$relativePath];
            return is_array($metadata) ? $metadata : null;
        }
        $composerFile = $packageDir . '/composer.json';
        if (!is_file($composerFile)) {
            return null;
        }
        $composer = json_decode(file_get_contents($composerFile), true);
        return is_array($composer) ? $composer : null;
    }

    private function registerPackage(string $packageDir, array $composer): void
    {
        $autoload = $composer['autoload'] ?? [];
        if (!empty($autoload['psr-4'])) {
            foreach ($autoload['psr-4'] as $namespace => $path) {
                $fullPath = $packageDir . '/' . $path;
                if (is_dir($fullPath)) {
                    $this->psr4Map[$namespace] = $fullPath;
                }
            }
        }
        if (!empty($autoload['classmap'])) {
            foreach ($autoload['classmap'] as $classPath) {
                $fullPath = rtrim($packageDir . '/' . $classPath, '/*');
                if (is_dir($fullPath)) {
                    $this->addClassesFromDirectory($fullPath);
                } elseif (is_file($fullPath)) {
                    $this->classMap[$this->extractClass($fullPath)] = $fullPath;
                }
            }
        }
    }

    private function addClassesFromDirectory(string $directory): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $className = $this->extractClass($file->getPathname());
            if ($className) {
                $this->classMap[$className] = $file->getPathname();
            }
        }
    }

    private function extractClass(string $filePath): ?string
    {
        static $cache = [];
        $realPath = realpath($filePath);
        if (!$realPath) {
            return null;
        }
        if (isset($cache[$realPath])) {
            return $cache[$realPath];
        }
        $content = file_get_contents($filePath);
        if (!$content) {
            return null;
        }
        $tokens = token_get_all($content);
        $namespace = null;
        $class = null;
        for ($i = 0; $i < count($tokens); $i++) {
            if (is_array($tokens[$i])) {
                if ($tokens[$i][0] === T_NAMESPACE) {
                    $namespace = '';
                    for ($j = $i + 2; $j < count($tokens); $j++) {
                        if (is_array($tokens[$j])) {
                            if ($tokens[$j][0] === T_STRING || $tokens[$j][0] === T_NAME_QUALIFIED) {
                                $namespace .= $tokens[$j][1];
                            }
                        } elseif ($tokens[$j] === ';' || is_array($tokens[$j])) {
                            break;
                        } else {
                            $namespace .= $tokens[$j];
                        }
                    }
                }
                if ($tokens[$i][0] === T_CLASS || $tokens[$i][0] === T_INTERFACE || $tokens[$i][0] === T_TRAIT) {
                    $class = $tokens[$i + 2][1] ?? null;
                    break;
                }
            }
        }
        $result = $class ? ($namespace ? $namespace . '\\' : '') . $class : null;
        $cache[$realPath] = $result;
        return $result;
    }

    private function loadModule(string $packageDir): void
    {
        $moduleFile = $packageDir . '/module.php';
        if (file_exists($moduleFile)) {
            require_once $moduleFile;
        }
    }

    private function relativePath(string $absolutePath): ?string
    {
        $normalizedBase = rtrim(str_replace('\\', '/', $this->basePath), '/');
        $normalizedPath = str_replace('\\', '/', $absolutePath);
        if (!str_starts_with($normalizedPath, $normalizedBase . '/')) {
            return null;
        }
        return substr($normalizedPath, strlen($normalizedBase) + 1);
    }

    public function loadClass(string $className): bool
    {
        if (isset($this->classMap[$className])) {
            require_once $this->classMap[$className];
            return class_exists($className, false) || interface_exists($className, false) || trait_exists($className, false);
        }
        foreach ($this->psr4Map as $namespace => $path) {
            if (str_starts_with($className, $namespace)) {
                $relativeClass = substr($className, strlen($namespace));
                $filePath = $path . str_replace('\\', '/', $relativeClass) . '.php';
                if (file_exists($filePath)) {
                    require_once $filePath;
                    return class_exists($className, false) || interface_exists($className, false) || trait_exists($className, false);
                }
            }
        }
        return false;
    }
}

$corePackagesPath = dirname(__DIR__) . '/packages';
$modulesPath = dirname(__DIR__) . '/modules';
$basePath = dirname(__DIR__);

$autoloader = new MarkoAutoloader($corePackagesPath, $basePath, is_array($runtimeManifest) ? $runtimeManifest : []);
$autoloader->addPath($modulesPath);
$autoloader->addPath($basePath . '/app');
$autoloader->build();
$autoloader->register();

return $autoloader;
PHP;

    $autoloadPath = $distDir . '/bootstrap/autoload.php';
    file_put_contents($autoloadPath, $productionAutoload);
    echo "   Updating autoload.php for production (no vendor)...\n";
}
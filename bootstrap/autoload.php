<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

class MarkoAutoloader
{
    private array $classMap = [];
    private array $psr4Map = [];

    public function __construct(
        private string $packagesPath,
        private string $basePath,
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
        spl_autoload_register([$this, 'loadClass'], true, false);
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

        foreach ($this->additionalPaths as $packagesPath) {
            $this->scanPath($packagesPath);
        }
    }

    private function scanPath(string $packagesPath): void
    {
        $packages = glob($packagesPath . '/*', GLOB_ONLYDIR);
        if (!$packages) {
            return;
        }

        foreach ($packages as $packageDir) {
            $composerFile = $packageDir . '/composer.json';
            if (!file_exists($composerFile)) {
                continue;
            }

            $composer = json_decode(file_get_contents($composerFile), true);
            if (!$composer) {
                continue;
            }

            $this->registerPackage($packageDir, $composer);
            $this->loadModule($packageDir);
        }
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
                } elseif (file_exists($fullPath)) {
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

        /** @var SplFileInfo $file */
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

        $result = null;
        if ($class) {
            $result = ($namespace ? $namespace . '\\' : '') . $class;
        }

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
$myPackagesPath = dirname(__DIR__) . '/packages';
$modulesPath = dirname(__DIR__) . '/modules';
$basePath = dirname(__DIR__);

$autoloader = new MarkoAutoloader($corePackagesPath, $basePath);
$autoloader->addPath($modulesPath);
$autoloader->build();
$autoloader->register();

return $autoloader;
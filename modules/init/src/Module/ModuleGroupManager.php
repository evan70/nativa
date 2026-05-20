<?php

declare(strict_types=1);

namespace App\Init\Module;

use Marko\Core\Container\Container;
use Marko\Core\Module\ModuleManifest;
use Marko\Core\Path\ProjectPaths;
use Psr\Log\LoggerInterface;

/**
 * Manages module groups, route-based binding, and idle eviction.
 * Supports persistent removal via state file.
 */
class ModuleGroupManager implements ModuleGroupManagerInterface
{
    /** @var array<string, ModuleGroup> */
    private array $groups = [];

    /** @var array<string, bool> */
    private array $activeGroups = [];

    /** @var array<string, ModuleManifest> */
    private array $manifests = [];

    private ?string $stateFile = null;
    private ?LoggerInterface $logger = null;

    public function __construct(
        private Container $container,
        // @phpstan-ignore-next-line property.unused
        private ?string $defaultIdleTimeout = '5m',
        // @phpstan-ignore-next-line property.unused
        private bool $evictionEnabled = true,
    ) {}

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function registerGroup(ModuleManifest $manifest): void
    {
        // Read extra.marko.* metadata directly from module's composer.json
        // (upstream ModuleManifest no longer carries these custom properties)
        $meta = $this->readModuleMetadata($manifest->path);

        if ($meta['group'] === null) {
            return;
        }

        // Load removed groups from state
        $removedGroups = $this->loadRemovedGroups();
        if (in_array($meta['group'], $removedGroups)) {
            return;
        }

        $group = new ModuleGroup(
            name: $meta['group'],
            moduleName: $manifest->name,
            routes: $meta['routes'],
            idleTimeout: $meta['idleTimeout'],
            isCore: $meta['isCore'],
        );

        $this->groups[$meta['group']] = $group;
        $this->manifests[$meta['group']] = $manifest;

        if ($meta['isCore']) {
            $this->activeGroups[$meta['group']] = true;
        }
    }

    /**
     * Read extra.marko metadata from a module's composer.json.
     *
     * @return array{group: string|null, routes: array<string>, idleTimeout: string|null, isCore: bool}
     */
    private function readModuleMetadata(string $modulePath): array
    {
        $composerPath = $modulePath . '/composer.json';
        /** @var array<string, mixed> $data */
        $data = [];

        if (is_file($composerPath)) {
            $contents = file_get_contents($composerPath);
            if ($contents !== false) {
                $decoded = json_decode($contents, true);
                if (is_array($decoded)) {
                    $data = $decoded;
                }
            }
        }

        /** @var array<string, mixed> $marko */
        $marko = is_array($data['extra'] ?? null) ? ($data['extra']['marko'] ?? []) : [];

        $groupVal = $marko['group'] ?? null;
        /** @var array<string> $routesVal */
        $routesVal = $marko['routes'] ?? [];
        $timeoutVal = $marko['idleTimeout'] ?? null;
        $isCoreVal = $marko['isCore'] ?? false;

        return [
            'group' => is_string($groupVal) ? $groupVal : null,
            'routes' => $routesVal,
            'idleTimeout' => is_string($timeoutVal) ? $timeoutVal : null,
            'isCore' => is_bool($isCoreVal) ? $isCoreVal : false,
        ];
    }

    /**
     * @return array<string>
     */
    private function loadRemovedGroups(): array
    {
        // Lazy init state file path
        if ($this->stateFile === null) {
            /** @var ProjectPaths $paths */
            $paths = $this->container->get(ProjectPaths::class);
            $this->stateFile = $paths->base . '/storage/framework/module-groups.json';
        }

        if (!is_file($this->stateFile)) {
            return [];
        }
        
        $content = file_get_contents($this->stateFile);
        if ($content === false) {
            return [];
        }
        /** @var array{removed?: array<string>}|null $data */
        $data = json_decode($content, true);
        
        return is_array($data) ? ($data['removed'] ?? []) : [];
    }

    /**
     * @param array<string> $removed
     */
    private function saveRemovedGroups(array $removed): void
    {
        if ($this->stateFile === null) {
            /** @var ProjectPaths $paths */
            $paths = $this->container->get(ProjectPaths::class);
            $this->stateFile = $paths->base . '/storage/framework/module-groups.json';
        }

        $dir = dirname($this->stateFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $data = ['removed' => $removed];
        file_put_contents($this->stateFile, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function markUsed(string $groupName): void
    {
        if (!isset($this->groups[$groupName])) {
            return;
        }
        $this->groups[$groupName] = $this->groups[$groupName]->markUsed();
    }

    public function getGroupForRoute(string $path): ?string
    {
        foreach ($this->groups as $name => $group) {
            if ($group->routes === []) continue;
            foreach ($group->routes as $pattern) {
                if (fnmatch($pattern, $path) || fnmatch($pattern, '/' . ltrim($path, '/'))) {
                    return $name;
                }
            }
        }
        return null;
    }

    public function evictIfIdle(string $groupName, string $maxIdle): bool
    {
        if (!isset($this->groups[$groupName])) {
            return false;
        }

        $group = $this->groups[$groupName];

        // Never evict core groups
        if ($group->isCore) {
            return false;
        }

        // Must be active to evict
        if (!isset($this->activeGroups[$groupName])) {
            return false;
        }

        $effectiveTimeout = $group->getEffectiveTimeout($maxIdle);
        
        if ($group->isIdle($effectiveTimeout)) {
            $this->deactivateGroup($groupName);
            
            $this->logger?->info('ModuleGroupManager: Evicted idle group {group} (timeout: {timeout})', [
                'group' => $groupName,
                'timeout' => $effectiveTimeout,
            ]);
            
            return true;
        }

        return false;
    }

    public function evictAllIdle(string $maxIdle): array
    {
        $evicted = [];

        foreach ($this->groups as $name => $group) {
            if (!$group->isCore && isset($this->activeGroups[$name])) {
                if ($this->evictIfIdle($name, $maxIdle)) {
                    $evicted[] = $name;
                }
            }
        }

        return $evicted;
    }
    public function isCoreGroup(string $groupName): bool { return $this->groups[$groupName]->isCore ?? false; }

    /**
     * @return array<string, ModuleGroup>
     */
    public function getGroups(): array { return $this->groups; }
    public function getGroup(string $name): ?ModuleGroup { return $this->groups[$name] ?? null; }
    public function isGroupActive(string $groupName): bool { return $this->activeGroups[$groupName] ?? false; }

    public function activateGroup(string $groupName): void
    {
        if (!isset($this->manifests[$groupName])) return;
        $manifest = $this->manifests[$groupName];
        /** @var array<string, string> $bindings */
        $bindings = $manifest->bindings;
        foreach ($bindings as $interface => $implementation) {
            $this->container->bind($interface, $implementation);
        }
        $this->activeGroups[$groupName] = true;
        $this->markUsed($groupName);
    }

    public function deactivateGroup(string $groupName): void
    {
        if (!isset($this->manifests[$groupName])) return;
        $manifest = $this->manifests[$groupName];
        /** @var array<string, string> $bindings */
        $bindings = $manifest->bindings;
        foreach ($bindings as $interface => $implementation) {
            $this->unbind($interface);
        }
        unset($this->activeGroups[$groupName]);
    }

    private function unbind(string $interface): bool
    {
        $ref = new \ReflectionClass(Container::class);
        $prop = $ref->getProperty('bindings');
        /** @var array<string, mixed> $bindings */
        $bindings = $prop->getValue($this->container);

        if (isset($bindings[$interface])) {
            unset($bindings[$interface]);
            $prop->setValue($this->container, $bindings);
            return true;
        }

        return false;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function configureEviction(array $config): void {}

    /**
     * @return array<int, array{name: string, module: string, routes: array<string>, isCore: bool, isActive: bool, lastUsed: string, idleTimeout: string|null}>
     */
    public function getSummary(): array
    {
        $summary = [];
        foreach ($this->groups as $name => $group) {
            $summary[] = [
                'name' => $name,
                'module' => $group->moduleName,
                'routes' => $group->routes,
                'isCore' => $group->isCore,
                'isActive' => $this->isGroupActive($name),
                'lastUsed' => $group->lastUsed->format(\DateTimeInterface::ATOM),
                'idleTimeout' => $group->idleTimeout,
            ];
        }
        return $summary;
    }

    public function removeGroup(string $name): void
    {
        if (!isset($this->groups[$name])) return;
        $group = $this->groups[$name];
        if ($group->isCore) return;

        unset($this->groups[$name], $this->manifests[$name], $this->activeGroups[$name]);

        $removed = $this->loadRemovedGroups();
        if (!in_array($name, $removed)) {
            $removed[] = $name;
            $this->saveRemovedGroups($removed);
        }

        $this->logger?->info('ModuleGroupManager: Removed group {group}', ['group' => $name]);
    }
}

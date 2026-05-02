<?php

declare(strict_types=1);

namespace App\Init\Module;

use Marko\Core\Container\ContainerInterface;
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
        private ContainerInterface $container,
        private ?string $defaultIdleTimeout = '5m',
        private bool $evictionEnabled = true,
    ) {}

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function registerGroup(ModuleManifest $manifest): void
    {
        if ($manifest->group === null) {
            return;
        }

        // Load removed groups from state
        $removedGroups = $this->loadRemovedGroups();
        if (in_array($manifest->group, $removedGroups)) {
            return;
        }

        $group = new ModuleGroup(
            name: $manifest->group,
            moduleName: $manifest->name,
            routes: $manifest->routes,
            idleTimeout: $manifest->idleTimeout,
            isCore: $manifest->isCore,
        );

        $this->groups[$manifest->group] = $group;
        $this->manifests[$manifest->group] = $manifest;
        
        if ($manifest->isCore) {
            $this->activeGroups[$manifest->group] = true;
        }
    }

    private function loadRemovedGroups(): array
    {
        // Lazy init state file path
        if ($this->stateFile === null) {
            $paths = $this->container->get(ProjectPaths::class);
            $this->stateFile = $paths->base . '/storage/framework/module-groups.json';
        }

        if (!is_file($this->stateFile)) {
            return [];
        }
        
        $content = file_get_contents($this->stateFile);
        $data = json_decode($content, true);
        
        return $data['removed'] ?? [];
    }

    private function saveRemovedGroups(array $removed): void
    {
        if ($this->stateFile === null) {
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
    public function getGroups(): array { return $this->groups; }
    public function getGroup(string $name): ?ModuleGroup { return $this->groups[$name] ?? null; }
    public function isGroupActive(string $groupName): bool { return $this->activeGroups[$groupName] ?? false; }

    public function activateGroup(string $groupName): void
    {
        if (!isset($this->manifests[$groupName])) return;
        $manifest = $this->manifests[$groupName];
        foreach ($manifest->bindings as $interface => $implementation) {
            if (is_string($implementation)) $this->container->bind($interface, $implementation);
        }
        $this->activeGroups[$groupName] = true;
        $this->markUsed($groupName);
    }

    public function deactivateGroup(string $groupName): void
    {
        if (!isset($this->manifests[$groupName])) return;
        $manifest = $this->manifests[$groupName];
        foreach ($manifest->bindings as $interface => $implementation) {
            if (is_string($interface)) $this->container->unbind($interface);
        }
        unset($this->activeGroups[$groupName]);
    }

    public function configureEviction(array $config): void {}

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
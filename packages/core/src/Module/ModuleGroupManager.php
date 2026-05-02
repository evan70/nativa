<?php

declare(strict_types=1);

namespace Marko\Core\Module;

use Marko\Core\Container\Container;
use Marko\Core\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Manages module groups, route-based binding, and idle eviction.
 */
class ModuleGroupManager implements ModuleGroupManagerInterface
{
    /** @var array<string, ModuleGroup> */
    private array $groups = [];

    /** @var array<string, bool> */
    private array $activeGroups = [];

    /** @var array<string, ModuleManifest> */
    private array $manifests = [];

    /** @var array<string, string> */
    private array $evictionConfig = [
        'enabled' => true,
        'defaultIdleTimeout' => '5m',
        'checkInterval' => '1m',
    ];

    private ?LoggerInterface $logger = null;

    public function __construct(
        private ContainerInterface $container,
        private ?string $defaultIdleTimeout = '5m',
        private bool $evictionEnabled = true,
    ) {}

    /**
     * Set logger for debugging.
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Register a module group from a module manifest.
     */
    public function registerGroup(ModuleManifest $manifest): void
    {
        // Skip if no group defined
        if ($manifest->group === null) {
            $this->logger?->debug('ModuleGroupManager: No group for module {module}', [
                'module' => $manifest->name,
            ]);
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
        
        // Core groups are active by default
        if ($manifest->isCore) {
            $this->activeGroups[$manifest->group] = true;
        }

        $this->logger?->debug('ModuleGroupManager: Registered group {group} for module {module} (isCore: {isCore}, routes: {routes})', [
            'group' => $manifest->group,
            'module' => $manifest->name,
            'isCore' => $manifest->isCore,
            'routes' => $manifest->routes,
        ]);
    }

    /**
     * Mark a group as used (called when route matches).
     */
    public function markUsed(string $groupName): void
    {
        if (!isset($this->groups[$groupName])) {
            return;
        }

        $this->groups[$groupName] = $this->groups[$groupName]->markUsed();
        
        $this->logger?->debug('ModuleGroupManager: Marked group {group} as used', [
            'group' => $groupName,
        ]);
    }

    /**
     * Find which group matches a route path.
     */
    public function getGroupForRoute(string $path): ?string
    {
        foreach ($this->groups as $name => $group) {
            if ($group->routes === []) {
                continue;
            }

            foreach ($group->routes as $pattern) {
                // Support fnmatch patterns like /admin/* or *.admin/*
                if (fnmatch($pattern, $path) || fnmatch($pattern, '/' . ltrim($path, '/'))) {
                    $this->logger?->debug('ModuleGroupManager: Route {path} matched group {group} via pattern {pattern}', [
                        'path' => $path,
                        'group' => $name,
                        'pattern' => $pattern,
                    ]);
                    return $name;
                }
            }
        }

        return null;
    }

    /**
     * Evict a group if it's been idle longer than maxIdle.
     *
     * @return bool True if group was evicted
     */
    public function evictIfIdle(string $groupName, string $maxIdle): bool
    {
        if (!isset($this->groups[$groupName])) {
            return false;
        }

        $group = $this->groups[$groupName];

        // Never evict core groups
        if ($group->isCore) {
            $this->logger?->debug('ModuleGroupManager: Skipping eviction of core group {group}', [
                'group' => $groupName,
            ]);
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

    /**
     * Evict all groups that have been idle longer than maxIdle.
     *
     * @return array<string> List of evicted group names
     */
    public function evictAllIdle(string $maxIdle): array
    {
        $evicted = [];

        foreach ($this->groups as $name => $group) {
            if (!$group->isCore && $this->evictIfIdle($name, $maxIdle)) {
                $evicted[] = $name;
            }
        }

        $this->logger?->debug('ModuleGroupManager: Evicted {count} idle groups: {groups}', [
            'count' => count($evicted),
            'groups' => $evicted,
        ]);

        return $evicted;
    }

    /**
     * Check if a group is a core group (never evicted).
     */
    public function isCoreGroup(string $groupName): bool
    {
        return $this->groups[$groupName]->isCore ?? false;
    }

    /**
     * Get all registered groups.
     *
     * @return array<string, ModuleGroup>
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * Get a specific group by name.
     */
    public function getGroup(string $name): ?ModuleGroup
    {
        return $this->groups[$name] ?? null;
    }

    /**
     * Check if a group is currently bound/active.
     */
    public function isGroupActive(string $groupName): bool
    {
        return $this->activeGroups[$groupName] ?? false;
    }

    /**
     * Activate a group's bindings in the container.
     */
    public function activateGroup(string $groupName): void
    {
        if (!isset($this->manifests[$groupName])) {
            $this->logger?->warning('ModuleGroupManager: No manifest for group {group}', [
                'group' => $groupName,
            ]);
            return;
        }

        $this->logger?->debug('ModuleGroupManager: Activating group {group}', [
            'group' => $groupName,
        ]);

        $manifest = $this->manifests[$groupName];
        $container = $this->container;

        // Bind interfaces to implementations
        foreach ($manifest->bindings as $interface => $implementation) {
            if (is_string($implementation)) {
                $container->bind($interface, $implementation);
            } elseif (is_callable($implementation)) {
                $container->bind($interface, $implementation);
            }
            
            $this->logger?->debug('ModuleGroupManager: Bound {interface} -> {implementation}', [
                'interface' => $interface,
                'implementation' => is_string($implementation) ? $implementation : 'closure',
            ]);
        }

        // Register singletons
        foreach ($manifest->singletons as $service) {
            if (is_string($service)) {
                $container->singleton($service);
            } elseif (is_callable($service)) {
                // For callable singletons, we register as binding
                $container->bind($service, $service);
            }
            
            $this->logger?->debug('ModuleGroupManager: Registered singleton {service}', [
                'service' => $service,
            ]);
        }

        $this->activeGroups[$groupName] = true;
        $this->markUsed($groupName);

        $this->logger?->info('ModuleGroupManager: Activated group {group} with {bindings} bindings', [
            'group' => $groupName,
            'bindings' => count($manifest->bindings),
        ]);
    }

    /**
     * Deactivate a group (unbind its bindings).
     */
    public function deactivateGroup(string $groupName): void
    {
        if (!isset($this->manifests[$groupName])) {
            return;
        }

        $this->logger?->debug('ModuleGroupManager: Deactivating group {group}', [
            'group' => $groupName,
        ]);

        $manifest = $this->manifests[$groupName];
        $container = $this->container;

        // Unbind interfaces
        foreach ($manifest->bindings as $interface => $implementation) {
            if (is_string($interface)) {
                $container->unbind($interface);
                
                $this->logger?->debug('ModuleGroupManager: Unbound {interface}', [
                    'interface' => $interface,
                ]);
            }
        }

        // Unbind singletons
        foreach ($manifest->singletons as $service) {
            if (is_string($service)) {
                $container->unbindSingleton($service);
                
                $this->logger?->debug('ModuleGroupManager: Unbound singleton {service}', [
                    'service' => $service,
                ]);
            }
        }

        unset($this->activeGroups[$groupName]);

        $this->logger?->info('ModuleGroupManager: Deactivated group {group}', [
            'group' => $groupName,
        ]);
    }

    /**
     * Configure eviction settings.
     */
    public function configureEviction(array $config): void
    {
        $this->evictionConfig = array_merge($this->evictionConfig, $config);
        
        $this->logger?->debug('ModuleGroupManager: Configured eviction: {config}', [
            'config' => $this->evictionConfig,
        ]);
    }

    /**
     * Get a summary of all groups for debugging.
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
}
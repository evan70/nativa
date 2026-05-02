<?php

declare(strict_types=1);

namespace App\Init\Module;

use Psr\Container\ContainerInterface;

/**
 * Interface for managing module groups, their bindings, and idle eviction.
 */
interface ModuleGroupManagerInterface
{
    /**
     * Register a module group from a module manifest.
     */
    public function registerGroup(ModuleManifest $manifest): void;

    /**
     * Mark a group as used (called when route matches).
     */
    public function markUsed(string $groupName): void;

    /**
     * Find which group matches a route path.
     */
    public function getGroupForRoute(string $path): ?string;

    /**
     * Evict a group if it's been idle longer than maxIdle.
     *
     * @return bool True if group was evicted
     */
    public function evictIfIdle(string $groupName, string $maxIdle): bool;

    /**
     * Evict all groups that have been idle longer than maxIdle.
     *
     * @return array<string> List of evicted group names
     */
    public function evictAllIdle(string $maxIdle): array;

    /**
     * Check if a group is a core group (never evicted).
     */
    public function isCoreGroup(string $groupName): bool;

    /**
     * Get all registered groups.
     *
     * @return array<string, ModuleGroup>
     */
    public function getGroups(): array;

    /**
     * Get a specific group by name.
     */
    public function getGroup(string $name): ?ModuleGroup;

    /**
     * Check if a group is currently bound/active.
     */
    public function isGroupActive(string $groupName): bool;

    /**
     * Activate a group's bindings in the container.
     */
    public function activateGroup(string $groupName): void;

    /**
     * Deactivate a group (unbind its bindings).
     */
    public function deactivateGroup(string $groupName): void;
}
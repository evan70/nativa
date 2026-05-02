<?php

declare(strict_types=1);

namespace Marko\Core\Module\Tests;

use Marko\Core\Module\ModuleGroup;
use Marko\Core\Module\ModuleGroupManager;
use Marko\Core\Module\ModuleGroupManagerInterface;
use Marko\Core\Module\ModuleManifest;
use PHPUnit\Framework\TestCase;

beforeEach(function (): void {
    $this->container = new \Marko\Core\Container\Container();
    $this->manager = new ModuleGroupManager($this->container);
});

describe('ModuleGroup', function (): void {
    it('creates with default values', function (): void {
        $group = new ModuleGroup(
            name: 'test',
            moduleName: 'marko/test',
        );

        expect($group->name)->toBe('test');
        expect($group->moduleName)->toBe('marko/test');
        expect($group->routes)->toBe([]);
        expect($group->idleTimeout)->toBeNull();
        expect($group->isCore)->toBe(false);
    });

    it('creates with all values', function (): void {
        $group = new ModuleGroup(
            name: 'admin',
            moduleName: 'marko/admin',
            routes: ['/admin/*'],
            idleTimeout: '5m',
            isCore: false,
        );

        expect($group->name)->toBe('admin');
        expect($group->routes)->toBe(['/admin/*']);
        expect($group->idleTimeout)->toBe('5m');
        expect($group->isCore)->toBe(false);
    });

    it('marks used and updates timestamp', function (): void {
        $group = new ModuleGroup(
            name: 'test',
            moduleName: 'marko/test',
        );

        $before = $group->lastUsed;
        sleep(1); // Ensure time difference
        
        $marked = $group->markUsed();
        
        expect($marked->lastUsed)->toBeGreaterThan($before);
    });

    it('detects idle timeout', function (): void {
        // Create a group with lastUsed in the past
        $oldTime = new \DateTimeImmutable('-10 minutes');
        
        $group = new ModuleGroup(
            name: 'test',
            moduleName: 'marko/test',
            idleTimeout: '5m',
            lastUsed: $oldTime,
        );

        expect($group->isIdle('5m'))->toBe(true);
    });

    it('returns effective timeout', function (): void {
        $group = new ModuleGroup(
            name: 'test',
            moduleName: 'marko/test',
            idleTimeout: '10m',
        );

        expect($group->getEffectiveTimeout('5m'))->toBe('10m');
        expect($group->getEffectiveTimeout(null))->toBe('10m');
    });

    it('falls back to default when no timeout set', function (): void {
        $group = new ModuleGroup(
            name: 'test',
            moduleName: 'marko/test',
        );

        expect($group->getEffectiveTimeout('5m'))->toBe('5m');
    });
});

describe('ModuleGroupManager', function (): void {
    it('registers a group from manifest', function (): void {
        $manifest = new ModuleManifest(
            name: 'marko/admin',
            version: '1.0.0',
            group: 'admin',
            routes: ['/admin/*'],
            idleTimeout: '5m',
            isCore: false,
        );

        $this->manager->registerGroup($manifest);

        expect(count($this->manager->getGroups()))->toBe(1);
        
        $group = $this->manager->getGroup('admin');
        expect($group?->name)->toBe('admin');
        expect($group?->moduleName)->toBe('marko/admin');
    });

    it('skips registration when no group defined', function (): void {
        $manifest = new ModuleManifest(
            name: 'marko/test',
            version: '1.0.0',
        );

        $this->manager->registerGroup($manifest);

        expect(count($this->manager->getGroups()))->toBe(0);
    });

    it('marks core groups as active by default', function (): void {
        $manifest = new ModuleManifest(
            name: 'marko/core',
            version: '1.0.0',
            group: 'core',
            isCore: true,
        );

        $this->manager->registerGroup($manifest);

        expect($this->manager->isGroupActive('core'))->toBe(true);
        expect($this->manager->isCoreGroup('core'))->toBe(true);
    });

    it('finds group for route path', function (): void {
        $manifest = new ModuleManifest(
            name: 'marko/admin',
            version: '1.0.0',
            group: 'admin',
            routes: ['/admin/*'],
            isCore: false,
        );

        $this->manager->registerGroup($manifest);

        expect($this->manager->getGroupForRoute('/admin/dashboard'))->toBe('admin');
        expect($this->manager->getGroupForRoute('/admin/users'))->toBe('admin');
        expect($this->manager->getGroupForRoute('/other'))->toBeNull();
    });

    it('supports fnmatch patterns', function (): void {
        $manifest = new ModuleManifest(
            name: 'marko/api',
            version: '1.0.0',
            group: 'api',
            routes: ['/api/*', '/api/v1/*'],
            isCore: false,
        );

        $this->manager->registerGroup($manifest);

        expect($this->manager->getGroupForRoute('/api/users'))->toBe('api');
        expect($this->manager->getGroupForRoute('/api/v1/users'))->toBe('api');
    });

    it('checks if group is core', function (): void {
        $coreManifest = new ModuleManifest(
            name: 'marko/core',
            version: '1.0.0',
            group: 'core',
            isCore: true,
        );
        
        $nonCoreManifest = new ModuleManifest(
            name: 'marko/admin',
            version: '1.0.0',
            group: 'admin',
            isCore: false,
        );

        $this->manager->registerGroup($coreManifest);
        $this->manager->registerGroup($nonCoreManifest);

        expect($this->manager->isCoreGroup('core'))->toBe(true);
        expect($this->manager->isCoreGroup('admin'))->toBe(false);
    });
});

describe('ModuleGroupManager eviction', function (): void {
    it('evicts idle non-core groups', function (): void {
        // Register non-core group
        $manifest = new ModuleManifest(
            name: 'marko/admin',
            version: '1.0.0',
            group: 'admin',
            routes: ['/admin/*'],
            idleTimeout: '1m', // 1 minute
            isCore: false,
        );

        $this->manager->registerGroup($manifest);
        
        // Manually set lastUsed to 10 minutes ago (simulate old timestamp)
        $oldTime = new \DateTimeImmutable('-10 minutes');
        
        // Use reflection to set lastUsed directly
        $ref = new \ReflectionClass($this->manager);
        $prop = $ref->getProperty('groups');
        $prop->setAccessible(true);
        $groups = $prop->getValue($this->manager);
        $groups['admin'] = new ModuleGroup(
            name: 'admin',
            moduleName: 'marko/admin',
            routes: ['/admin/*'],
            idleTimeout: '1m',
            isCore: false,
            lastUsed: $oldTime,
        );
        $prop->setValue($this->manager, $groups);

        $evicted = $this->manager->evictIfIdle('admin', '5m');

        expect($evicted)->toBe(true);
    });

    it('skips eviction of core groups', function (): void {
        $manifest = new ModuleManifest(
            name: 'marko/core',
            version: '1.0.0',
            group: 'core',
            isCore: true,
        );

        $this->manager->registerGroup($manifest);

        $evicted = $this->manager->evictIfIdle('core', '5m');

        expect($evicted)->toBe(false);
        expect($this->manager->isGroupActive('core'))->toBe(true);
    });

    it('evicts all idle groups at once', function (): void {
        // Create old non-core groups
        $oldTime = new \DateTimeImmutable('-10 minutes');

        $adminManifest = new ModuleManifest(
            name: 'marko/admin',
            version: '1.0.0',
            group: 'admin',
            routes: ['/admin/*'],
            idleTimeout: '1m',
            isCore: false,
        );
        
        $authManifest = new ModuleManifest(
            name: 'marko/auth',
            version: '1.0.0',
            group: 'auth',
            routes: ['/login'],
            idleTimeout: '1m',
            isCore: false,
        );

        $coreManifest = new ModuleManifest(
            name: 'marko/core',
            version: '1.0.0',
            group: 'core',
            isCore: true,
        );

        $this->manager->registerGroup($adminManifest);
        $this->manager->registerGroup($authManifest);
        $this->manager->registerGroup($coreManifest);

        // Override lastUsed for non-core groups via reflection
        $ref = new \ReflectionClass($this->manager);
        $prop = $ref->getProperty('groups');
        $prop->setAccessible(true);
        $groups = [
            'admin' => new ModuleGroup('admin', 'marko/admin', ['/admin/*'], '1m', false, $oldTime),
            'auth' => new ModuleGroup('auth', 'marko/auth', ['/login'], '1m', false, $oldTime),
            'core' => new ModuleGroup('core', 'marko/core', [], null, true),
        ];
        $prop->setValue($this->manager, $groups);

        $evicted = $this->manager->evictAllIdle('5m');

        expect(count($evicted))->toBe(2);
        expect(in_array('admin', $evicted))->toBe(true);
        expect(in_array('auth', $evicted))->toBe(true);
    });
});
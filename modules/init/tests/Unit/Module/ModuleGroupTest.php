<?php

declare(strict_types=1);

namespace App\Init\Tests;

use App\Init\Module\ModuleGroup;
use App\Init\Module\ModuleGroupManager;
use App\Init\Module\ModuleGroupManagerInterface;
use Marko\Core\Module\ModuleManifest as Manifest;
use PHPUnit\Framework\TestCase;
use Marko\Core\Path\ProjectPaths;

beforeEach(function (): void {
    $this->container = new \App\Init\Container\Container();
    
    // Mock ProjectPaths for state file
    $paths = new ProjectPaths(basePath: sys_get_temp_dir());
    $this->container->instance(ProjectPaths::class, $paths);
    
    $this->manager = new ModuleGroupManager($this->container);
    $this->tempDir = sys_get_temp_dir() . '/marko_test_' . uniqid();
    mkdir($this->tempDir, 0755, true);
});

afterEach(function (): void {
    if (isset($this->tempDir) && is_dir($this->tempDir)) {
        removeDir($this->tempDir);
    }
});

function createModuleJson(string $path, array $marko): void {
    file_put_contents($path . '/composer.json', json_encode([
        'extra' => ['marko' => $marko]
    ]));
}

function removeDir(string $dir): void {
    if (!is_dir($dir)) return;
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? removeDir("$dir/$file") : unlink("$dir/$file");
    }
    rmdir($dir);
}

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
        expect($group->getEffectiveTimeout())->toBe('10m');
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
        createModuleJson($this->tempDir, [
            'group' => 'admin',
            'routes' => ['/admin/*'],
            'idleTimeout' => '5m',
            'isCore' => false,
        ]);

        $manifest = new Manifest(
            name: 'marko/admin',
            version: '1.0.0',
            path: $this->tempDir,
        );

        $this->manager->registerGroup($manifest);

        expect(count($this->manager->getGroups()))->toBe(1);
        
        $group = $this->manager->getGroup('admin');
        expect($group?->name)->toBe('admin');
        expect($group?->moduleName)->toBe('marko/admin');
    });

    it('skips registration when no group defined', function (): void {
        createModuleJson($this->tempDir, []);

        $manifest = new Manifest(
            name: 'marko/test',
            version: '1.0.0',
            path: $this->tempDir,
        );

        $this->manager->registerGroup($manifest);

        expect(count($this->manager->getGroups()))->toBe(0);
    });

    it('marks core groups as active by default', function (): void {
        createModuleJson($this->tempDir, [
            'group' => 'core',
            'isCore' => true,
        ]);

        $manifest = new Manifest(
            name: 'marko/core',
            version: '1.0.0',
            path: $this->tempDir,
        );

        $this->manager->registerGroup($manifest);

        expect($this->manager->isGroupActive('core'))->toBe(true);
        expect($this->manager->isCoreGroup('core'))->toBe(true);
    });

    it('finds group for route path', function (): void {
        createModuleJson($this->tempDir, [
            'group' => 'admin',
            'routes' => ['/admin/*'],
            'isCore' => false,
        ]);

        $manifest = new Manifest(
            name: 'marko/admin',
            version: '1.0.0',
            path: $this->tempDir,
        );

        $this->manager->registerGroup($manifest);

        expect($this->manager->getGroupForRoute('/admin/dashboard'))->toBe('admin');
        expect($this->manager->getGroupForRoute('/admin/users'))->toBe('admin');
        expect($this->manager->getGroupForRoute('/other'))->toBeNull();
    });

    it('supports fnmatch patterns', function (): void {
        createModuleJson($this->tempDir, [
            'group' => 'api',
            'routes' => ['/api/*', '/api/v1/*'],
            'isCore' => false,
        ]);

        $manifest = new Manifest(
            name: 'marko/api',
            version: '1.0.0',
            path: $this->tempDir,
        );

        $this->manager->registerGroup($manifest);

        expect($this->manager->getGroupForRoute('/api/users'))->toBe('api');
        expect($this->manager->getGroupForRoute('/api/v1/users'))->toBe('api');
    });

    it('checks if group is core', function (): void {
        $coreDir = $this->tempDir . '/core';
        $adminDir = $this->tempDir . '/admin';
        mkdir($coreDir);
        mkdir($adminDir);

        createModuleJson($coreDir, [
            'group' => 'core',
            'isCore' => true,
        ]);
        
        createModuleJson($adminDir, [
            'group' => 'admin',
            'isCore' => false,
        ]);

        $coreManifest = new Manifest(
            name: 'marko/core',
            version: '1.0.0',
            path: $coreDir,
        );
        
        $nonCoreManifest = new Manifest(
            name: 'marko/admin',
            version: '1.0.0',
            path: $adminDir,
        );

        $this->manager->registerGroup($coreManifest);
        $this->manager->registerGroup($nonCoreManifest);

        expect($this->manager->isCoreGroup('core'))->toBe(true);
        expect($this->manager->isCoreGroup('admin'))->toBe(false);
    });
});

describe('ModuleGroupManager eviction', function (): void {
    it('evicts idle non-core groups', function (): void {
        createModuleJson($this->tempDir, [
            'group' => 'admin',
            'routes' => ['/admin/*'],
            'idleTimeout' => '1m',
            'isCore' => false,
        ]);

        $manifest = new Manifest(
            name: 'marko/admin',
            version: '1.0.0',
            path: $this->tempDir,
        );

        $this->manager->registerGroup($manifest);
        
        // Manually set lastUsed to 10 minutes ago (simulate old timestamp)
        $oldTime = new \DateTimeImmutable('-10 minutes');
        
        // Use reflection to set lastUsed directly
        $ref = new \ReflectionClass($this->manager);
        $prop = $ref->getProperty('groups');
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

        // Also need to set manifests property via reflection because registerGroup sets it
        // but we are overriding groups. Actually registerGroup already set manifests.

        // And we need to mark it as active to be eligible for eviction
        $activeProp = $ref->getProperty('activeGroups');
        $activeProp->setValue($this->manager, ['admin' => true]);

        $evicted = $this->manager->evictIfIdle('admin', '5m');

        expect($evicted)->toBe(true);
    });

    it('skips eviction of core groups', function (): void {
        createModuleJson($this->tempDir, [
            'group' => 'core',
            'isCore' => true,
        ]);

        $manifest = new Manifest(
            name: 'marko/core',
            version: '1.0.0',
            path: $this->tempDir,
        );

        $this->manager->registerGroup($manifest);

        $evicted = $this->manager->evictIfIdle('core', '5m');

        expect($evicted)->toBe(false);
        expect($this->manager->isGroupActive('core'))->toBe(true);
    });

    it('evicts all idle groups at once', function (): void {
        $adminDir = $this->tempDir . '/admin';
        $authDir = $this->tempDir . '/auth';
        $coreDir = $this->tempDir . '/core';
        mkdir($adminDir);
        mkdir($authDir);
        mkdir($coreDir);

        createModuleJson($adminDir, [
            'group' => 'admin',
            'routes' => ['/admin/*'],
            'idleTimeout' => '1m',
            'isCore' => false,
        ]);
        
        createModuleJson($authDir, [
            'group' => 'auth',
            'routes' => ['/login'],
            'idleTimeout' => '1m',
            'isCore' => false,
        ]);

        createModuleJson($coreDir, [
            'group' => 'core',
            'isCore' => true,
        ]);

        $adminManifest = new Manifest(name: 'marko/admin', version: '1.0.0', path: $adminDir);
        $authManifest = new Manifest(name: 'marko/auth', version: '1.0.0', path: $authDir);
        $coreManifest = new Manifest(name: 'marko/core', version: '1.0.0', path: $coreDir);

        $this->manager->registerGroup($adminManifest);
        $this->manager->registerGroup($authManifest);
        $this->manager->registerGroup($coreManifest);

        // Override lastUsed for non-core groups via reflection
        $oldTime = new \DateTimeImmutable('-10 minutes');
        $ref = new \ReflectionClass($this->manager);
        $prop = $ref->getProperty('groups');
        $groups = [
            'admin' => new ModuleGroup('admin', 'marko/admin', ['/admin/*'], '1m', false, $oldTime),
            'auth' => new ModuleGroup('auth', 'marko/auth', ['/login'], '1m', false, $oldTime),
            'core' => new ModuleGroup('core', 'marko/core', [], null, true),
        ];
        $prop->setValue($this->manager, $groups);

        // Mark them active
        $activeProp = $ref->getProperty('activeGroups');
        $activeProp->setValue($this->manager, ['admin' => true, 'auth' => true, 'core' => true]);

        $evicted = $this->manager->evictAllIdle('5m');

        expect(count($evicted))->toBe(2);
        expect(in_array('admin', $evicted))->toBe(true);
        expect(in_array('auth', $evicted))->toBe(true);
    });
});
<?php

declare(strict_types=1);

use Marko\Core\Path\ProjectPaths;

it('uses vendor as packages root when vendor marko packages are installed', function (): void {
    $basePath = sys_get_temp_dir() . '/project-paths-vendor-' . uniqid();
    mkdir($basePath . '/vendor/marko/core', 0777, true);

    try {
        $paths = new ProjectPaths($basePath);

        expect($paths->packages)->toBe($basePath . '/vendor')
            ->and($paths->vendor)->toBe($basePath . '/vendor');
    } finally {
        rmdir($basePath . '/vendor/marko/core');
        rmdir($basePath . '/vendor/marko');
        rmdir($basePath . '/vendor');
        rmdir($basePath);
    }
});

it('falls back to packages directory when vendor is absent', function (): void {
    $basePath = sys_get_temp_dir() . '/project-paths-packages-' . uniqid();
    mkdir($basePath . '/packages/core', 0777, true);

    try {
        $paths = new ProjectPaths($basePath);

        expect($paths->packages)->toBe($basePath . '/packages')
            ->and($paths->vendor)->toBe($basePath . '/packages');
    } finally {
        rmdir($basePath . '/packages/core');
        rmdir($basePath . '/packages');
        rmdir($basePath);
    }
});

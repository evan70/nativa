<?php

declare(strict_types=1);

use Marko\Cli\ProjectFinder;

it('finds a project rooted by vendor marko packages', function (): void {
    $basePath = sys_get_temp_dir() . '/project-finder-vendor-' . uniqid();
    mkdir($basePath . '/vendor/marko/core', 0777, true);
    mkdir($basePath . '/nested/deeper', 0777, true);

    try {
        $finder = new ProjectFinder();

        expect($finder->find($basePath . '/nested/deeper'))->toBe($basePath);
    } finally {
        rmdir($basePath . '/nested/deeper');
        rmdir($basePath . '/nested');
        rmdir($basePath . '/vendor/marko/core');
        rmdir($basePath . '/vendor/marko');
        rmdir($basePath . '/vendor');
        rmdir($basePath);
    }
});

it('finds a project rooted by flat packages directory', function (): void {
    $basePath = sys_get_temp_dir() . '/project-finder-packages-' . uniqid();
    mkdir($basePath . '/packages/core', 0777, true);
    mkdir($basePath . '/nested/deeper', 0777, true);

    try {
        $finder = new ProjectFinder();

        expect($finder->find($basePath . '/nested/deeper'))->toBe($basePath);
    } finally {
        rmdir($basePath . '/nested/deeper');
        rmdir($basePath . '/nested');
        rmdir($basePath . '/packages/core');
        rmdir($basePath . '/packages');
        rmdir($basePath);
    }
});

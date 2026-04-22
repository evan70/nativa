<?php

declare(strict_types=1);

use Marko\Core\Module\ManifestParser;
use Marko\Core\Module\ModuleDiscovery;

function createMarkoModule(
    string $path,
    string $name,
): void {
    mkdir($path, 0777, true);

    file_put_contents($path . '/composer.json', json_encode([
        'name' => $name,
        'autoload' => [
            'psr-4' => [
                'Test\\Package\\' => 'src/',
            ],
        ],
        'extra' => [
            'marko' => [
                'module' => true,
            ],
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

it('discovers modules from composer vendor layout', function (): void {
    $basePath = sys_get_temp_dir() . '/module-discovery-vendor-' . uniqid();
    $modulePath = $basePath . '/vendor/marko/example';
    createMarkoModule($modulePath, 'marko/example');

    try {
        $modules = (new ModuleDiscovery(new ManifestParser()))->discoverInVendor($basePath . '/vendor');

        expect($modules)->toHaveCount(1)
            ->and($modules[0]->name)->toBe('marko/example')
            ->and($modules[0]->path)->toBe($modulePath)
            ->and($modules[0]->source)->toBe('vendor');
    } finally {
        unlink($modulePath . '/composer.json');
        rmdir($modulePath);
        rmdir($basePath . '/vendor/marko');
        rmdir($basePath . '/vendor');
        rmdir($basePath);
    }
});

it('discovers modules from flat packages layout', function (): void {
    $basePath = sys_get_temp_dir() . '/module-discovery-packages-' . uniqid();
    $modulePath = $basePath . '/packages/example';
    createMarkoModule($modulePath, 'marko/example');

    try {
        $modules = (new ModuleDiscovery(new ManifestParser()))->discoverInVendor($basePath . '/packages');

        expect($modules)->toHaveCount(1)
            ->and($modules[0]->name)->toBe('marko/example')
            ->and($modules[0]->path)->toBe($modulePath)
            ->and($modules[0]->source)->toBe('vendor');
    } finally {
        unlink($modulePath . '/composer.json');
        rmdir($modulePath);
        rmdir($basePath . '/packages');
        rmdir($basePath);
    }
});

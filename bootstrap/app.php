<?php

declare(strict_types=1);

use Marko\Core\Application;
use Marko\Core\Path\ProjectPaths;

$basePath = dirname(__DIR__);
$appPath = dirname(__DIR__) . '/app';

$app = new Application(
    vendorPath: ProjectPaths::resolvePackagesRoot($basePath),
    modulesPath: dirname(__DIR__) . '/modules',
    appPath: $appPath,
    basePath: $basePath,
);

$app->initialize();

return $app;

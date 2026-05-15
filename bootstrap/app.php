<?php

declare(strict_types=1);

use App\Init\Bootstrap\Paths;
use Marko\Core\Application;

$basePath = dirname(__DIR__);
$appPath = dirname(__DIR__) . '/app';

$app = new Application(
    vendorPath: Paths::resolvePackagesRoot($basePath),
    modulesPath: dirname(__DIR__) . '/modules',
    appPath: $appPath,
);

$app->initialize();

// Register application in container for module bindings
$app->container->instance(Application::class, $app);

return $app;

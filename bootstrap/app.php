<?php

declare(strict_types=1);

// Load Composer autoload if available (dev environment)
$composerAutoload = dirname(__DIR__) . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
    require_once $composerAutoload;
}

// Load environment variables using Marko Env package
$envLoader = new \Marko\Env\EnvLoader();
$envLoader->load(dirname(__DIR__));

// Load env() helper function
$envFunctions = is_dir(dirname(__DIR__) . '/vendor/marko/env')
    ? dirname(__DIR__) . '/vendor/marko/env/src/functions.php'
    : dirname(__DIR__) . '/packages/env/src/functions.php';

if (file_exists($envFunctions)) {
    require_once $envFunctions;
}

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

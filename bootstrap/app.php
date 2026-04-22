<?php

declare(strict_types=1);

use Marko\Core\Application;

$basePath = dirname(__DIR__);
$vendorPath = dirname(__DIR__) . '/vendor';
$appPath = dirname(__DIR__) . '/app';

$app = new Application(
    vendorPath: $vendorPath,
    modulesPath: dirname(__DIR__) . '/modules',
    appPath: $appPath,
);

$app->initialize();

return $app;
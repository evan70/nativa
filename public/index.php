<?php

declare(strict_types=1);

// Otherwise, bootstrap the core application
require __DIR__ . '/../bootstrap/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->handleRequest();

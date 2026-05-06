<?php

declare(strict_types=1);

// Serve static files directly
$path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$file = __DIR__ . $path;

// If static file exists, serve it
if ($path !== '/' && file_exists($file) && is_file($file)) {
    return false;
}

// Otherwise, bootstrap the core application
require __DIR__ . '/../bootstrap/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->handleRequest();

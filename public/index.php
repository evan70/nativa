<?php

declare(strict_types=1);

// When used as router script for PHP built-in server:
// Return false to let server handle static files, otherwise bootstrap app

$path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$file = __DIR__ . $path;

// Serve static files directly (return false lets PHP server handle it)
if ($path !== '/' && file_exists($file) && is_file($file)) {
    // Let the built-in server handle static files
    return false;
}

// Otherwise, bootstrap the core application
require __DIR__ . '/../bootstrap/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->handleRequest();

<?php

declare(strict_types=1);

// Serve static files directly with correct MIME types
$path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$path = strtok($path, '?');
$file = __DIR__ . $path;

if ($path !== '/' && file_exists($file) && is_file($file)) {
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $mimeTypes = [
        'js'   => 'application/javascript',
        'mjs'  => 'application/javascript',
        'css'  => 'text/css',
        'json' => 'application/json',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'ttf'  => 'font/ttf',
        'otf'  => 'font/otf',
        'eot'  => 'application/vnd.ms-fontobject',
        'svg'  => 'image/svg+xml',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'ico'  => 'image/x-icon',
        'webp' => 'image/webp',
        'map'  => 'application/json',
    ];

    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
    }

    // Cache control based on file type
    $cacheRules = [
        'woff2' => 'public, max-age=31536000, immutable',
        'woff'  => 'public, max-age=31536000, immutable',
        'ttf'   => 'public, max-age=31536000, immutable',
        'css'   => 'public, max-age=31536000, immutable',
        'js'    => 'public, max-age=31536000, immutable',
        'json'  => 'public, max-age=31536000, immutable',
        'png'   => 'public, max-age=2592000',
        'jpg'   => 'public, max-age=2592000',
        'jpeg'  => 'public, max-age=2592000',
        'gif'   => 'public, max-age=2592000',
        'svg'   => 'public, max-age=2592000',
        'webp'  => 'public, max-age=2592000',
        'ico'   => 'public, max-age=2592000',
        'map'   => 'public, max-age=31536000, immutable',
    ];
    if (isset($cacheRules[$ext])) {
        header('Cache-Control: ' . $cacheRules[$ext]);
    }

    readfile($file);
    exit;
}

// Otherwise, bootstrap the core application
require __DIR__ . '/../bootstrap/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->handleRequest();

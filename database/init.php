#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Database Initialization CLI
 *
 * Usage:
 *   php database/init.php                      # Init all databases
 *   php database/init.php cardboard             # Init specific database
 *   php database/init.php --seed                # Include seed data
 *   php database/init.php --force               # Skip confirmation
 *   php database/init.php cardboard --seed      # Specific DB with seed
 *   php database/init.php --no-seed             # Schema only, no seed data
 */

chdir(dirname(__DIR__));

require __DIR__ . '/../bootstrap/autoload.php';

use App\Database\InitRunner;

// Parse arguments
$args = array_slice($argv, 1);
$seed = true;
$force = false;
$dbName = null;

foreach ($args as $arg) {
    if ($arg === '--seed') {
        $seed = true;
    } elseif ($arg === '--no-seed') {
        $seed = false;
    } elseif ($arg === '--force' || $arg === '--no-interaction' || $arg === '-y') {
        $force = true;
    } elseif ($arg === '--help' || $arg === '-h') {
        echo "Usage: php database/init.php [database] [--seed|--no-seed] [--force]\n";
        echo "\n";
        echo "Options:\n";
        echo "  database            Database to init: cardboard, articles, portfolio (default: all)\n";
        echo "  --seed              Include seed data (default)\n";
        echo "  --no-seed           Schema only, skip seed data\n";
        echo "  --force, -y         Skip confirmation prompt\n";
        echo "  --no-interaction    Same as --force (CI compatibility)\n";
        echo "  --help, -h          Show this help\n";
        exit(0);
    } elseif ($arg === '--all') {
        $dbName = null;
    } elseif (str_starts_with($arg, '--')) {
        echo "Unknown option: $arg\n";
        echo "Use --help for usage.\n";
        exit(1);
    } else {
        $dbName = $arg;
    }
}

// Confirm unless forced
if (!$force) {
    $target = $dbName ?? 'all databases';
    echo "This will DROP and RECREATE all tables in $target.\n";
    echo "All existing data will be LOST!\n";
    echo "Continue? [y/N] ";

    $handle = fopen('php://stdin', 'r');
    if ($handle === false) {
        echo "Error: Cannot read input.\n";
        exit(1);
    }

    $response = strtolower(trim(fgets($handle)));
    fclose($handle);

    if ($response !== 'y' && $response !== 'yes') {
        echo "Cancelled.\n";
        exit(0);
    }
}

$runner = new InitRunner();

try {
    if ($dbName !== null) {
        $runner->run($dbName, $seed);
    } else {
        $runner->runAll($seed);
    }

    echo "\n✅ Database initialization complete.\n";
} catch (\Exception $e) {
    echo "\n❌ Error: {$e->getMessage()}\n";
    exit(1);
}

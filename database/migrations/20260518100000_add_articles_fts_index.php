<?php

declare(strict_types=1);

/**
 * Migration: Add FTS4 full-text search index to articles table.
 *
 * This migration creates the articles_fts virtual table for existing databases
 * that were initialized before the FTS index was added to articles.sql.
 *
 * Run via:
 *   php database/migrations/20260518100000_add_articles_fts_index.php
 *
 * Or via Marko CLI (after registering):
 *   php marko db:migrate
 */

$basePath = dirname(__DIR__, 2);
$dbPath = $basePath . '/storage/data/articles.db';

if (!file_exists($dbPath)) {
    echo "Blog database not found at: $dbPath\n";
    echo "Nothing to migrate.\n";
    exit(0);
}

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if articles_fts already exists
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='articles_fts'");
    $exists = $stmt->fetchColumn();

    if ($exists) {
        echo "✓ articles_fts table already exists. Skipping.\n";
        exit(0);
    }

    // Create FTS4 virtual table
    $pdo->exec("
        CREATE VIRTUAL TABLE \"articles_fts\" USING fts4(
            title,
            excerpt,
            content
        )
    ");
    echo "✓ Created articles_fts virtual table (FTS4)\n";

    // Populate from existing articles
    $count = $pdo->exec("
        INSERT INTO articles_fts(docid, title, excerpt, content)
        SELECT id, title, excerpt, content FROM articles
    ");
    echo "✓ Indexed $count existing articles\n";

    echo "\nMigration complete. Full-text search is now available on the articles table.\n";
    exit(0);

} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

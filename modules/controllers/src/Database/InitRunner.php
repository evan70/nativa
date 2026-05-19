<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

/**
 * Dev-phase database initializer.
 *
 * Runs init SQL scripts that drop and recreate all tables for a given database.
 * Supports all module-based databases: cardboard, articles, portfolio.
 *
 * SQL files use a marker comment to separate schema from seed data:
 *   -- === SCHEMA END ===
 * Everything before the marker is schema DDL.
 * Everything after is seed data (skipped with --no-seed).
 */
final class InitRunner
{
    private string $dataDir;
    private string $initDir;

    /** @var array<string, string> Map of database name → filename (without .db) */
    private array $databases = [
        'cardboard' => 'cardboard',
        'articles' => 'articles',
        'portfolio' => 'portfolio',
    ];

    /** Marker in SQL files separating schema from seed data */
    private const string SEED_MARKER = '-- === SCHEMA END ===';

    public function __construct(?string $dataDir = null, ?string $initDir = null)
    {
        $this->dataDir = $dataDir ?? dirname(__DIR__, 4) . '/storage/data';
        $this->initDir = $initDir ?? dirname(__DIR__, 4) . '/database/init';
    }

    /**
     * Initialize a specific database with its SQL init script.
     */
    public function run(string $dbName, bool $seed = true): void
    {
        if (!isset($this->databases[$dbName])) {
            throw new \InvalidArgumentException("Unknown database: '$dbName'. Available: " . implode(', ', array_keys($this->databases)));
        }

        $dbFile = $this->dataDir . '/' . $dbName . '.db';
        $sqlFile = $this->initDir . '/' . $dbName . '.sql';

        if (!file_exists($sqlFile)) {
            throw new \RuntimeException("Init SQL file not found: $sqlFile");
        }

        echo "  Initializing database: $dbName ($dbFile)\n";

        $sql = file_get_contents($sqlFile);
        if ($sql === false || $sql === '') {
            throw new \RuntimeException("Failed to read init SQL file: $sqlFile");
        }

        // Connect to SQLite database
        $pdo = new PDO('sqlite:' . $dbFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Enable foreign keys
        $pdo->exec('PRAGMA foreign_keys = ON');

        // Extract schema part (before marker) and seed part (after marker)
        $parts = explode(self::SEED_MARKER, $sql, 2);
        $schemaSql = $parts[0];
        $seedSql = $parts[1] ?? '';

        // Execute schema statements
        $schemaStatements = $this->splitSqlStatements($schemaSql);
        $executed = 0;

        foreach ($schemaStatements as $stmt) {
            $stmt = trim($stmt);
            if ($stmt === '') {
                continue;
            }

            $pdo->exec($stmt);
            $executed++;
        }

        echo "  ├─ Schema: $executed statements\n";

        // Execute seed data if requested
        if ($seed && $seedSql !== '') {
            $seedStatements = $this->splitSqlStatements($seedSql);
            $seedExecuted = 0;

            foreach ($seedStatements as $stmt) {
                $stmt = trim($stmt);
                if ($stmt === '') {
                    continue;
                }

                $pdo->exec($stmt);
                $seedExecuted++;
            }

            echo "  └─ Seed: $seedExecuted statements\n";
        } else {
            echo "  └─ Seed: skipped\n";
        }

        echo "  ✅ $dbName.db initialized\n";
    }

    /**
     * Initialize all databases.
     */
    public function runAll(bool $seed = true): void
    {
        foreach (array_keys($this->databases) as $dbName) {
            $this->run($dbName, $seed);
            echo "\n";
        }
    }

    /**
     * Get list of available databases.
     *
     * @return array<string, string>
     */
    public function getDatabases(): array
    {
        return $this->databases;
    }

    /**
     * Split SQL text into individual statements.
     * Removes single-line comments and splits by semicolons.
     *
     * @return array<string>
     */
    private function splitSqlStatements(string $sql): array
    {
        // Remove single-line comments (-- ...)
        $sql = preg_replace('/^--.*$/m', '', $sql);

        // Remove blank lines
        $sql = preg_replace('/^\s*$/m', '', $sql);

        // Split by semicolons
        $parts = explode(';', $sql);

        return array_filter(array_map('trim', $parts), fn (string $s) => $s !== '');
    }
}

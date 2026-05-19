<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class DatabaseConnectionTest extends TestCase
{
    private string $cardboardDbPath;

    protected function setUp(): void
    {
        $this->cardboardDbPath = dirname(__DIR__, 3) . '/storage/data/cardboard.db';
    }

    public function testCardboardDatabaseFileExists(): void
    {
        $this->assertFileExists($this->cardboardDbPath, 'Cardboard database file not found at ' . $this->cardboardDbPath);
    }

    public function testCardboardDatabaseIsValidSqlite(): void
    {
        if (!file_exists($this->cardboardDbPath)) {
            $this->markTestSkipped('Cardboard database file does not exist');
        }

        $pdo = new PDO('sqlite:' . $this->cardboardDbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Verify it's a valid SQLite database
        $result = $pdo->query('SELECT name FROM sqlite_master WHERE type="table" ORDER BY name');
        $tables = $result->fetchAll(PDO::FETCH_COLUMN);

        $this->assertIsArray($tables);
        $this->assertNotEmpty($tables, 'Cardboard database should have tables after migration');
    }

    public function testCardboardDatabaseHasExpectedTables(): void
    {
        if (!file_exists($this->cardboardDbPath)) {
            $this->markTestSkipped('Cardboard database file does not exist');
        }

        $pdo = new PDO('sqlite:' . $this->cardboardDbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name LIKE 'cardboard_%' ORDER BY name");
        $tables = $result->fetchAll(PDO::FETCH_COLUMN);

        $this->assertContains('cardboard_settings', $tables, 'Expected cardboard_settings table');
        $this->assertContains('cardboard_audit_log', $tables, 'Expected cardboard_audit_log table');
    }
}

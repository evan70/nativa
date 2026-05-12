<?php

declare(strict_types=1);

namespace App\Blog\Database;

use Marko\Database\Connection\ConnectionInterface;
use Marko\Database\Connection\PDOSqliteConnection;

class ArticleConnection implements ConnectionInterface
{
    private PDOSqliteConnection $connection;

    public function __construct()
    {
        $dbPath = dirname(__DIR__, 4) . '/storage/data/articles.db';
        $this->connection = new PDOSqliteConnection($dbPath);
    }

    public function getConnection(): PDOSqliteConnection
    {
        return $this->connection;
    }

    public function query(string $sql, array $params = []): array
    {
        return $this->connection->query($sql, $params);
    }

    public function execute(string $sql, array $params = []): int
    {
        return $this->connection->execute($sql, $params);
    }

    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }
}
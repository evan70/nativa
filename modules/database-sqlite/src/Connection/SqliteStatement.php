<?php

declare(strict_types=1);

namespace Marko\Sqlite\Connection;

use Marko\Database\Connection\StatementInterface;
use PDOStatement;

class SqliteStatement implements StatementInterface
{
    public function __construct(
        private readonly PDOStatement $statement,
    ) {}

    public function execute(array $bindings = []): bool
    {
        return $this->statement->execute($bindings);
    }

    public function fetchAll(): array
    {
        return $this->statement->fetchAll() ?: [];
    }

    public function fetch(): ?array
    {
        $row = $this->statement->fetch();
        return $row !== false ? $row : null;
    }

    public function rowCount(): int
    {
        return $this->statement->rowCount();
    }
}
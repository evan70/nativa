<?php

declare(strict_types=1);

namespace Marko\Database\Sqlite\Connection;

use Marko\Database\Connection\StatementInterface;
use PDOStatement;

class SqliteStatement implements StatementInterface
{
    public function __construct(
        private readonly PDOStatement $statement,
    ) {}

    /**
     * @param array<string, mixed> $bindings
     */
    public function execute(array $bindings = []): bool
    {
        return $this->statement->execute($bindings);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function fetchAll(): array
    {
        /** @var list<array<string, mixed>> $result */
        $result = $this->statement->fetchAll() ?: [];
        return $result;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetch(): ?array
    {
        $row = $this->statement->fetch();
        /** @var array<string, mixed>|null $result */
        $result = $row !== false ? $row : null;
        return $result;
    }

    public function rowCount(): int
    {
        return $this->statement->rowCount();
    }
}
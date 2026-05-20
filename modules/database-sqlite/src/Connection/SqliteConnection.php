<?php

declare(strict_types=1);

namespace Marko\Database\Sqlite\Connection;

use InvalidArgumentException;
use Marko\Database\Connection\ConnectionInterface;
use Marko\Database\Connection\StatementInterface;
use Marko\Database\Connection\TransactionInterface;
use PDO;
use PDOException;
use PDOStatement;

class SqliteConnection implements ConnectionInterface, TransactionInterface
{
    private ?PDO $pdo = null;

    private bool $inTransaction = false;

    public function __construct(
        private readonly string $path,
    ) {}

    public function connect(): void
    {
        if ($this->pdo !== null) {
            return;
        }

        try {
            $this->pdo = new PDO('sqlite:' . $this->path);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new SqliteException(
                message: 'Failed to connect to SQLite database',
                previous: $e,
            );
        }
    }

    public function disconnect(): void
    {
        $this->pdo = null;
    }

    public function isConnected(): bool
    {
        return $this->pdo !== null;
    }

    /**
     * @param array<string, mixed> $bindings
     * @return list<array<string, mixed>>
     */
    public function query(
        string $sql,
        array $bindings = [],
    ): array {
        $this->connect();
        \assert($this->pdo !== null);

        try {
            /** @var PDOStatement $stmt */
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($bindings);

            /** @var list<array<string, mixed>> $result */
            $result = $stmt->fetchAll() ?: [];
            return $result;
        } catch (PDOException $e) {
            throw new SqliteException(
                message: "Query failed: {$sql}",
                previous: $e,
            );
        }
    }

    /**
     * @param array<string, mixed> $bindings
     */
    public function execute(
        string $sql,
        array $bindings = [],
    ): int {
        $this->connect();
        \assert($this->pdo !== null);

        try {
            /** @var PDOStatement $stmt */
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($bindings);

            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new SqliteException(
                message: "Execute failed: {$sql}",
                previous: $e,
            );
        }
    }

    public function prepare(string $sql): StatementInterface
    {
        $this->connect();
        \assert($this->pdo !== null);

        return new SqliteStatement($this->pdo->prepare($sql));
    }

    public function lastInsertId(): int
    {
        $this->connect();
        \assert($this->pdo !== null);

        return (int) $this->pdo->lastInsertId();
    }

    public function beginTransaction(): void
    {
        $this->connect();
        \assert($this->pdo !== null);
        $this->pdo->beginTransaction();
        $this->inTransaction = true;
    }

    public function commit(): void
    {
        if (!$this->inTransaction) {
            throw new SqliteException('No active transaction to commit');
        }

        \assert($this->pdo !== null);
        $this->pdo->commit();
        $this->inTransaction = false;
    }

    public function rollback(): void
    {
        if (!$this->inTransaction) {
            throw new SqliteException('No active transaction to rollback');
        }

        \assert($this->pdo !== null);
        $this->pdo->rollBack();
        $this->inTransaction = false;
    }

    public function inTransaction(): bool
    {
        return $this->inTransaction;
    }

    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();

            return $result;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }
}
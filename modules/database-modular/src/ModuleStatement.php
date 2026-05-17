<?php

declare(strict_types=1);

namespace App\DatabaseModular;

use Marko\Database\Connection\StatementInterface;
use PDOStatement;

/**
 * Simple wrapper for PDOStatement to satisfy StatementInterface
 */
class ModuleStatement implements StatementInterface
{
    private PDOStatement $statement;
    
    public function __construct(PDOStatement $statement)
    {
        $this->statement = $statement;
    }
    
    /**
     * @param array<string, mixed> $bindings
     */
    public function execute(array $bindings = []): bool
    {
        return $this->statement->execute($bindings);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetch(): ?array
    {
        /** @var array<string, mixed>|false $result */
        $result = $this->statement->fetch();
        return $result !== false ? $result : null;
    }
    
    /**
     * @return array<array<string, mixed>>
     */
    public function fetchAll(): array
    {
        /** @var array<array<string, mixed>> $result */
        $result = $this->statement->fetchAll();
        return $result;
    }
    
    public function rowCount(): int
    {
        return $this->statement->rowCount();
    }
}

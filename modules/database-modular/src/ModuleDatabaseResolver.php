<?php

declare(strict_types=1);

namespace App\DatabaseModular;

use App\DatabaseModular\Contracts\ModuleDatabaseResolverInterface;
use Marko\Database\Connection\ConnectionInterface;
use Marko\Database\Connection\StatementInterface;
use PDO;
use PDOStatement;

class ModuleDatabaseResolver implements ModuleDatabaseResolverInterface
{
    /**
     * @var array<string, string> - module name => database name mapping
     */
    private array $moduleMapping;
    
    /**
     * @var string - path to storage/data directory
     */
    private string $storagePath;
    
    /**
     * @param array<string, string> $moduleMapping
     */
    public function __construct(
        array $moduleMapping = [],
        ?string $storagePath = null
    ) {
        $this->moduleMapping = $moduleMapping;
        $this->storagePath = $storagePath ?? dirname(__DIR__, 3) . '/storage/data';
    }
    
    public function getDatabasePath(string $moduleName): string
    {
        if (!isset($this->moduleMapping[$moduleName])) {
            // Fallback to module name as database name
            return $this->storagePath . '/' . $moduleName . '.db';
        }
        
        return $this->storagePath . '/' . $this->moduleMapping[$moduleName] . '.db';
    }
    
    public function hasOwnDatabase(string $moduleName): bool
    {
        return isset($this->moduleMapping[$moduleName]);
    }
    
    /**
     * @param mixed $container
     * @psalm-suppress MixedMethodCall
     */
    public function getConnection(string $moduleName, mixed $container): ConnectionInterface
    {
        // If module has its own database, use it
        if ($this->hasOwnDatabase($moduleName)) {
            return $this->createConnection($this->getDatabasePath($moduleName));
        }
        
        // Otherwise, use the default global connection
        // @phpstan-ignore-next-line
        return $container->get(ConnectionInterface::class);
    }
    
    public function getRegisteredModules(): array
    {
        return $this->moduleMapping;
    }
    
    /**
     * Create a PDO connection for the given database path
     */
    private function createConnection(string $dbPath): ConnectionInterface
    {
        // Create a simple PDO-based connection for the module's database
        // This is a simplified implementation - in production you'd want proper connection pooling
        
        $pdo = new PDO(
            'sqlite:' . $dbPath,
            null,
            null,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
        
        // Return a wrapper that implements ConnectionInterface
        return new ModuleConnection($pdo);
    }
}

/**
 * Simple wrapper for PDO to satisfy ConnectionInterface
 */
class ModuleConnection implements ConnectionInterface
{
    private ?PDO $pdo = null;
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * @param array<string, mixed> $params
     */
    /**
     * @param array<string, mixed> $params
     * @return array<array<string, mixed>>
     */
    public function query(string $sql, array $params = []): array
    {
        /** @var PDO $pdo */
        $pdo = $this->pdo;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        /** @var array<array<string, mixed>> $result */
        $result = $stmt->fetchAll();
        return $result;
    }
    
    /**
     * @param array<string, mixed> $params
     */
    public function execute(string $sql, array $params = []): int
    {
        /** @var PDO $pdo */
        $pdo = $this->pdo;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
    
    public function lastInsertId(): int
    {
        /** @var PDO $pdo */
        $pdo = $this->pdo;
        return (int) $pdo->lastInsertId();
    }
    
    public function prepare(string $sql): \Marko\Database\Connection\StatementInterface
    {
        /** @var PDO $pdo */
        $pdo = $this->pdo;
        $stmt = $pdo->prepare($sql);
        return new ModuleStatement($stmt);
    }
    
    public function connect(): void
    {
        // SQLite connects automatically
    }
    
    public function disconnect(): void
    {
        $this->pdo = null;
    }
    
    public function isConnected(): bool
    {
        return $this->pdo !== null;
    }
}

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
<?php

declare(strict_types=1);

namespace App\DatabaseModular;

use App\AppLogger;
use App\DatabaseModular\Contracts\ModuleDatabaseResolverInterface;
use Marko\Database\Connection\ConnectionInterface;
use Marko\Database\Connection\StatementInterface;
use Memcached;
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

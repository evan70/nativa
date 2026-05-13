<?php

declare(strict_types=1);

namespace App\DatabaseModular\Contracts;

use Marko\Database\Connection\ConnectionInterface;

interface ModuleDatabaseResolverInterface
{
    /**
     * Get database path for a specific module
     */
    public function getDatabasePath(string $moduleName): string;
    
    /**
     * Check if module has its own database
     */
    public function hasOwnDatabase(string $moduleName): bool;
    
    /**
     * Get connection for a specific module
     * Returns the module's own database connection or default connection
     */
    public function getConnection(string $moduleName, mixed $container): ConnectionInterface;
    
    /**
     * Get all registered modules with their databases
     * @return array<string, string>
     */
    public function getRegisteredModules(): array;
}
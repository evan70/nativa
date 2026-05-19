<?php

declare(strict_types=1);

namespace App\Database;

use App\DatabaseModular\Contracts\ModuleDatabaseResolverInterface;
use Marko\Database\Connection\ConnectionInterface;

/**
 * Connection class for the native/system database (nativa.db)
 * 
 * Used for system data: users, roles, permissions, sessions, etc.
 */
class NativaConnection
{
    private ?ConnectionInterface $connection = null;
    
    public function __construct(
        private readonly ModuleDatabaseResolverInterface $resolver
    ) {
        // DEBUG: Log NativaConnection initialization
        error_log('[NativaConnection] Initialized with resolver: ' . get_class($resolver));
    }
    
    public function getConnection(): ConnectionInterface
    {
        if ($this->connection === null) {
            $this->connection = $this->resolver->getConnection('nativa', null);
            
            // DEBUG: Log connection creation
            $dbPath = $this->resolver->getDatabasePath('nativa');
            error_log('[NativaConnection] Created connection to: ' . $dbPath);
        }
        
        return $this->connection;
    }
    
    /**
     * Get the database file path
     */
    public function getDatabasePath(): string
    {
        return $this->resolver->getDatabasePath('nativa');
    }
    
    /**
     * Check if this connection has its own database
     */
    public function hasOwnDatabase(): bool
    {
        return $this->resolver->hasOwnDatabase('nativa');
    }
}
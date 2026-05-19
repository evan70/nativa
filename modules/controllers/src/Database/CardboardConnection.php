<?php

declare(strict_types=1);

namespace App\Database;

use App\DatabaseModular\Contracts\ModuleDatabaseResolverInterface;
use Marko\Database\Connection\ConnectionInterface;

/**
 * Connection class for the main database (cardboard.db)
 *
 * Used for system data: users, roles, permissions, sessions, settings, etc.
 */
class CardboardConnection
{
    private ?ConnectionInterface $connection = null;

    public function __construct(
        private readonly ModuleDatabaseResolverInterface $resolver
    ) {
        error_log('[CardboardConnection] Initialized with resolver: ' . get_class($resolver));
    }

    public function getConnection(): ConnectionInterface
    {
        if ($this->connection === null) {
            $this->connection = $this->resolver->getConnection('cardboard', null);

            $dbPath = $this->resolver->getDatabasePath('cardboard');
            error_log('[CardboardConnection] Created connection to: ' . $dbPath);
        }

        return $this->connection;
    }

    /**
     * Get the database file path
     */
    public function getDatabasePath(): string
    {
        return $this->resolver->getDatabasePath('cardboard');
    }

    /**
     * Check if this connection has its own database
     */
    public function hasOwnDatabase(): bool
    {
        return $this->resolver->hasOwnDatabase('cardboard');
    }
}

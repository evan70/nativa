<?php

declare(strict_types=1);

namespace Marko\Mark\Database;

use App\DatabaseModular\Contracts\ModuleDatabaseResolverInterface;
use Marko\Database\Connection\ConnectionInterface;

class MarkConnection
{
    private ConnectionInterface $connection;

    public function __construct(ModuleDatabaseResolverInterface $resolver)
    {
        // Get the connection for the 'mark' module
        // We use a dummy container here or just rely on the resolver fallback
        // In this architecture, we pass null as container if we don't have it easily, 
        // or we get it from the resolver.
        $this->connection = $resolver->getConnection('mark', null);
    }

    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }
}

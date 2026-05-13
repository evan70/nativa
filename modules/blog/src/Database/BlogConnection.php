<?php

declare(strict_types=1);

namespace App\Blog\Database;

use App\DatabaseModular\Contracts\ModuleDatabaseResolverInterface;
use Marko\Database\Connection\ConnectionInterface;

class BlogConnection
{
    private ?ConnectionInterface $connection = null;
    
    public function __construct(
        private readonly ModuleDatabaseResolverInterface $resolver
    ) {}
    
    public function getConnection(): ConnectionInterface
    {
        if ($this->connection === null) {
            $this->connection = $this->resolver->getConnection('blog', null);
        }
        
        return $this->connection;
    }
}
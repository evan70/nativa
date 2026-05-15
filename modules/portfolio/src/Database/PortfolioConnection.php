<?php

declare(strict_types=1);

namespace App\Portfolio\Database;

use App\DatabaseModular\Contracts\ModuleDatabaseResolverInterface;
use Marko\Database\Connection\ConnectionInterface;

class PortfolioConnection
{
    private ?ConnectionInterface $connection = null;

    public function __construct(
        private readonly ModuleDatabaseResolverInterface $resolver,
    ) {}

    public function getConnection(): ConnectionInterface
    {
        if ($this->connection === null) {
            $this->connection = $this->resolver->getConnection('portfolio', null);
        }

        return $this->connection;
    }
}

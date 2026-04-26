<?php

declare(strict_types=1);

namespace App\App\Seed;

use Marko\Database\Connection\ConnectionInterface;
use Marko\Database\Seed\Seeder;
use Marko\Database\Seed\SeederInterface;
use Marko\Hashing\Contracts\HasherInterface;

#[Seeder(name: 'AdminUserSeeder', order: 1)]
class AdminUserSeeder implements SeederInterface
{
    public function __construct(
        private ConnectionInterface $connection,
        private HasherInterface $hasher,
    ) {}

    public function run(): void
    {
        $this->connection->execute(
            'INSERT INTO "admin_users" ("email", "password", "name", "isActive", "createdAt", "updatedAt") VALUES (?, ?, ?, ?, ?, ?)',
            [
                'admin@example.com',
                $this->hasher->hash('password'),
                'Administrator',
                '1',
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s'),
            ]
        );
    }
}

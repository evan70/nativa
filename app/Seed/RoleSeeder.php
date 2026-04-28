<?php

declare(strict_types=1);

namespace App\Seed;

use Marko\Database\Connection\ConnectionInterface;
use Marko\Database\Seed\Seeder;
use Marko\Database\Seed\SeederInterface;

#[Seeder(name: 'RoleSeeder', order: 2)]
class RoleSeeder implements SeederInterface
{
    public function __construct(
        private ConnectionInterface $connection,
    ) {}

    public function run(): void
    {
        // Insert admin role
        $this->connection->execute(
            'INSERT INTO "roles" ("name", "slug", "description", "isSuperAdmin", "createdAt", "updatedAt") VALUES (?, ?, ?, ?, ?, ?)',
            [
                'Administrator',
                'admin',
                'System Administrator',
                '1',
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s'),
            ]
        );

        $roleId = $this->connection->lastInsertId();

        // Find the mark user created by MarkSeeder
        $rows = $this->connection->query('SELECT id FROM marks WHERE email = ?', ['admin@example.com']);
        $user = $rows[0] ?? null;

        if ($user) {
            $this->connection->execute(
                'INSERT INTO "mark_roles" ("user_id", "role_id") VALUES (?, ?)',
                [(int)$user['id'], (int)$roleId]
            );
        }
    }
}

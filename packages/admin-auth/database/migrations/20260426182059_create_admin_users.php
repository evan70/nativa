<?php

declare(strict_types=1);

use Marko\Database\Connection\ConnectionInterface;
use Marko\Database\Migration\Migration;

return new class extends Migration {
    public function up(
        ConnectionInterface $connection,
    ): void {
        $this->execute($connection, <<<'SQL'
            CREATE TABLE "admin_users" ("id" INTEGER PRIMARY KEY AUTOINCREMENT, "email" TEXT NOT NULL, "password" TEXT NOT NULL, "name" TEXT NOT NULL, "rememberToken" TEXT, "isActive" TEXT NOT NULL DEFAULT 1, "createdAt" TEXT, "updatedAt" TEXT);
            SQL);
    }

    public function down(
        ConnectionInterface $connection,
    ): void {
        $this->execute($connection, <<<'SQL'
            DROP TABLE IF EXISTS "admin_users";
            SQL);
    }
};

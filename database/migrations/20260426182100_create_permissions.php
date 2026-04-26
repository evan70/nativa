<?php

declare(strict_types=1);

use Marko\Database\Connection\ConnectionInterface;
use Marko\Database\Migration\Migration;

return new class extends Migration {
    public function up(
        ConnectionInterface $connection,
    ): void {
        $this->execute($connection, <<<'SQL'
            CREATE TABLE "permissions" ("id" INTEGER PRIMARY KEY AUTOINCREMENT, "key" TEXT NOT NULL, "label" TEXT NOT NULL, "group" TEXT NOT NULL, "createdAt" TEXT);
            SQL);
    }

    public function down(
        ConnectionInterface $connection,
    ): void {
        $this->execute($connection, <<<'SQL'
            DROP TABLE IF EXISTS "permissions";
            SQL);
    }
};

<?php

declare(strict_types=1);

use Marko\Database\Connection\ConnectionInterface;
use Marko\Database\Migration\Migration;

return new class extends Migration {
    public function up(
        ConnectionInterface $connection,
    ): void {
        $this->execute($connection, <<<'SQL'
            CREATE TABLE "notifications" ("id" TEXT NOT NULL DEFAULT '', "type" TEXT NOT NULL DEFAULT '', "notifiableType" TEXT NOT NULL DEFAULT '', "notifiableId" TEXT NOT NULL DEFAULT '', "data" TEXT NOT NULL DEFAULT '', "readAt" TEXT, "createdAt" TEXT NOT NULL DEFAULT '', PRIMARY KEY ("id"));
            SQL);
    }

    public function down(
        ConnectionInterface $connection,
    ): void {
        $this->execute($connection, <<<'SQL'
            DROP TABLE IF EXISTS "notifications";
            SQL);
    }
};

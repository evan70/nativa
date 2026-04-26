<?php

declare(strict_types=1);

use Marko\Database\Connection\ConnectionInterface;
use Marko\Database\Migration\Migration;

return new class extends Migration {
    public function up(
        ConnectionInterface $connection,
    ): void {
        $this->execute($connection, <<<'SQL'
            CREATE TABLE "role_permissions" ("roleId" INTEGER NOT NULL, "permissionId" INTEGER NOT NULL);
            SQL);

        $this->execute($connection, <<<'SQL'
            CREATE UNIQUE INDEX "idx_role_permissions_unique" ON "role_permissions" ("roleId", "permissionId");
            SQL);
    }

    public function down(
        ConnectionInterface $connection,
    ): void {
        $this->execute($connection, <<<'SQL'
            DROP TABLE IF EXISTS "role_permissions";
            SQL);
    }
};

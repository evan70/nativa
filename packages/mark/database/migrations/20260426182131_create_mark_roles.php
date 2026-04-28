<?php

declare(strict_types=1);

use Marko\Database\Connection\ConnectionInterface;
use Marko\Database\Migration\Migration;

return new class extends Migration {
    public function up(
        ConnectionInterface $connection,
    ): void {
        $this->execute($connection, <<<'SQL'
            CREATE TABLE "mark_roles" (
                "user_id" INTEGER NOT NULL,
                "role_id" INTEGER NOT NULL,
                PRIMARY KEY ("user_id", "role_id"),
                FOREIGN KEY ("user_id") REFERENCES "marks" ("id") ON DELETE CASCADE,
                FOREIGN KEY ("role_id") REFERENCES "roles" ("id") ON DELETE CASCADE
            );
            SQL);

        $this->execute($connection, <<<'SQL'
            CREATE INDEX "idx_mark_roles_role_id" ON "mark_roles" ("role_id");
            SQL);
    }

    public function down(
        ConnectionInterface $connection,
    ): void {
        $this->execute($connection, <<<'SQL'
            DROP TABLE IF EXISTS "mark_roles";
            SQL);
    }
};

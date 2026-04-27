<?php

declare(strict_types=1);

use Marko\Database\Connection\ConnectionInterface;
use Marko\Database\Migration\Migration;

return new class extends Migration {
    public function up(
        ConnectionInterface $connection,
    ): void {
        $this->execute($connection, <<<'SQL'
            CREATE TABLE "articles" (
                "id" INTEGER PRIMARY KEY AUTOINCREMENT,
                "title" TEXT NOT NULL DEFAULT '',
                "slug" TEXT NOT NULL DEFAULT '',
                "excerpt" TEXT NOT NULL DEFAULT '',
                "content" TEXT NOT NULL DEFAULT '',
                "image" TEXT NOT NULL DEFAULT '',
                "status" TEXT NOT NULL DEFAULT 'published',
                "category_id" INTEGER,
                "published" TEXT NOT NULL DEFAULT '',
                "created_at" TEXT
            );
            SQL);
    }

    public function down(
        ConnectionInterface $connection,
    ): void {
        $this->execute($connection, <<<'SQL'
            DROP TABLE IF EXISTS "articles";
            SQL);
    }
};

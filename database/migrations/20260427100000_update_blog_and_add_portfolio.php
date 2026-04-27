<?php

declare(strict_types=1);

use Marko\Database\Connection\ConnectionInterface;
use Marko\Database\Migration\Migration;

return new class extends Migration {
    public function up(
        ConnectionInterface $connection,
    ): void {
        $this->execute($connection, 'CREATE TABLE "categories" ("id" INTEGER PRIMARY KEY AUTOINCREMENT, "name" TEXT NOT NULL, "slug" TEXT NOT NULL UNIQUE, "description" TEXT)');
        $this->execute($connection, 'CREATE TABLE "tags" ("id" INTEGER PRIMARY KEY AUTOINCREMENT, "name" TEXT NOT NULL, "slug" TEXT NOT NULL UNIQUE)');
        $this->execute($connection, 'CREATE TABLE "article_tags" ("article_id" INTEGER, "tag_id" INTEGER, PRIMARY KEY ("article_id", "tag_id"))');
        $this->execute($connection, 'CREATE TABLE "portfolio_items" ("id" INTEGER PRIMARY KEY AUTOINCREMENT, "title" TEXT NOT NULL, "slug" TEXT NOT NULL UNIQUE, "description" TEXT, "category" TEXT, "image" TEXT)');

        $this->execute($connection, 'ALTER TABLE "articles" ADD COLUMN "slug" TEXT NOT NULL DEFAULT ""');
        $this->execute($connection, 'ALTER TABLE "articles" ADD COLUMN "excerpt" TEXT NOT NULL DEFAULT ""');
        $this->execute($connection, 'ALTER TABLE "articles" ADD COLUMN "image" TEXT NOT NULL DEFAULT ""');
        $this->execute($connection, 'ALTER TABLE "articles" ADD COLUMN "status" TEXT NOT NULL DEFAULT "published"');
        $this->execute($connection, 'ALTER TABLE "articles" ADD COLUMN "category_id" INTEGER');
    }

    public function down(
        ConnectionInterface $connection,
    ): void {
        $this->execute($connection, 'DROP TABLE IF EXISTS "portfolio_items"');
        $this->execute($connection, 'DROP TABLE IF EXISTS "article_tags"');
        $this->execute($connection, 'DROP TABLE IF EXISTS "tags"');
        $this->execute($connection, 'DROP TABLE IF EXISTS "categories"');
    }
};

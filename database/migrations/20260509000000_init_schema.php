<?php

declare(strict_types=1);

use Marko\Database\Migration\Migration;
use Marko\Database\Connection\ConnectionInterface;

return new class extends Migration
{
    public function up(ConnectionInterface $connection): void
    {
        $connection->execute('CREATE TABLE IF NOT EXISTS "mark_users" (
            "id" INTEGER PRIMARY KEY AUTOINCREMENT,
            "email" TEXT NOT NULL,
            "password" TEXT NOT NULL,
            "name" TEXT NOT NULL,
            "rememberToken" TEXT,
            "isActive" TEXT NOT NULL DEFAULT 1,
            "createdAt" TEXT,
            "updatedAt" TEXT
        )');

        $connection->execute('CREATE TABLE IF NOT EXISTS "permissions" (
            "id" INTEGER PRIMARY KEY AUTOINCREMENT,
            "key" TEXT NOT NULL,
            "label" TEXT NOT NULL,
            "group" TEXT NOT NULL,
            "createdAt" TEXT
        )');

        $connection->execute('CREATE TABLE IF NOT EXISTS "sessions" (
            "id" TEXT PRIMARY KEY,
            "payload" TEXT NOT NULL,
            "last_activity" INTEGER NOT NULL
        )');

        $connection->execute('CREATE TABLE IF NOT EXISTS "roles" (
            "id" INTEGER PRIMARY KEY AUTOINCREMENT,
            "name" TEXT NOT NULL,
            "slug" TEXT NOT NULL,
            "description" TEXT,
            "isSuperAdmin" TEXT NOT NULL DEFAULT 0,
            "createdAt" TEXT,
            "updatedAt" TEXT
        )');

        $connection->execute('CREATE TABLE IF NOT EXISTS "role_permissions" (
            "roleId" INTEGER NOT NULL,
            "permissionId" INTEGER NOT NULL
        )');

        $connection->execute('CREATE UNIQUE INDEX IF NOT EXISTS "idx_role_permissions_unique"
            ON "role_permissions" ("roleId", "permissionId")');

        $connection->execute('CREATE TABLE IF NOT EXISTS "mark_roles" (
            "user_id" INTEGER NOT NULL,
            "role_id" INTEGER NOT NULL,
            PRIMARY KEY ("user_id", "role_id"),
            FOREIGN KEY ("user_id") REFERENCES "mark_users" ("id") ON DELETE CASCADE,
            FOREIGN KEY ("role_id") REFERENCES "roles" ("id") ON DELETE CASCADE
        )');

        $connection->execute('CREATE INDEX IF NOT EXISTS "idx_mark_roles_role_id"
            ON "mark_roles" ("role_id")');

        $connection->execute('CREATE TABLE IF NOT EXISTS "articles" (
            "id" INTEGER PRIMARY KEY AUTOINCREMENT,
            "title" TEXT NOT NULL DEFAULT \'\',
            "slug" TEXT NOT NULL DEFAULT \'\',
            "excerpt" TEXT NOT NULL DEFAULT \'\',
            "content" TEXT NOT NULL DEFAULT \'\',
            "image" TEXT NOT NULL DEFAULT \'\',
            "status" TEXT NOT NULL DEFAULT \'published\',
            "category_id" INTEGER,
            "published" TEXT NOT NULL DEFAULT \'\',
            "created_at" TEXT
        )');

        $connection->execute('CREATE TABLE IF NOT EXISTS "categories" (
            "id" INTEGER PRIMARY KEY AUTOINCREMENT,
            "name" TEXT NOT NULL,
            "slug" TEXT NOT NULL UNIQUE,
            "description" TEXT
        )');

        $connection->execute('CREATE TABLE IF NOT EXISTS "tags" (
            "id" INTEGER PRIMARY KEY AUTOINCREMENT,
            "name" TEXT NOT NULL,
            "slug" TEXT NOT NULL UNIQUE
        )');

        $connection->execute('CREATE TABLE IF NOT EXISTS "article_tags" (
            "article_id" INTEGER,
            "tag_id" INTEGER,
            PRIMARY KEY ("article_id", "tag_id")
        )');

        $connection->execute('CREATE TABLE IF NOT EXISTS "portfolio_items" (
            "id" INTEGER PRIMARY KEY AUTOINCREMENT,
            "title" TEXT NOT NULL DEFAULT \'\',
            "slug" TEXT NOT NULL UNIQUE,
            "subtitle" TEXT NOT NULL DEFAULT \'\',
            "description" TEXT NOT NULL DEFAULT \'\',
            "category" TEXT NOT NULL DEFAULT \'\',
            "role" TEXT NOT NULL DEFAULT \'\',
            "year" TEXT NOT NULL DEFAULT \'\',
            "stack" TEXT NOT NULL DEFAULT \'\',
            "image" TEXT NOT NULL DEFAULT \'\',
            "display_order" INTEGER DEFAULT 0
        )');

        $connection->execute('CREATE TABLE IF NOT EXISTS "notifications" (
            "id" TEXT NOT NULL DEFAULT \'\',
            "type" TEXT NOT NULL DEFAULT \'\',
            "notifiableType" TEXT NOT NULL DEFAULT \'\',
            "notifiableId" TEXT NOT NULL DEFAULT \'\',
            "data" TEXT NOT NULL DEFAULT \'\',
            "readAt" TEXT,
            "createdAt" TEXT NOT NULL DEFAULT \'\',
            PRIMARY KEY ("id")
        )');
    }

    public function down(ConnectionInterface $connection): void
    {
        $connection->execute('DROP TABLE IF EXISTS "article_tags"');
        $connection->execute('DROP TABLE IF EXISTS "mark_roles"');
        $connection->execute('DROP TABLE IF EXISTS "role_permissions"');
        $connection->execute('DROP TABLE IF EXISTS "notifications"');
        $connection->execute('DROP TABLE IF EXISTS "portfolio_items"');
        $connection->execute('DROP TABLE IF EXISTS "articles"');
        $connection->execute('DROP TABLE IF EXISTS "tags"');
        $connection->execute('DROP TABLE IF EXISTS "categories"');
        $connection->execute('DROP TABLE IF EXISTS "sessions"');
        $connection->execute('DROP TABLE IF EXISTS "permissions"');
        $connection->execute('DROP TABLE IF EXISTS "roles"');
        $connection->execute('DROP TABLE IF NOT EXISTS "mark_users"');
    }
};
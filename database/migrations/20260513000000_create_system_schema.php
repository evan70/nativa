<?php

declare(strict_types=1);

use Marko\Database\Migration\Migration;
use Marko\Database\Connection\ConnectionInterface;

/**
 * Migration: Create system schema for nativa.db
 * 
 * System tables: users, roles, permissions, sessions, migrations, notifications, portfolio_items
 * (Excludes: articles, categories, tags - those belong to articles.db)
 */
return new class extends Migration
{
    public function up(ConnectionInterface $connection): void
    {
        error_log('[Migration] Creating system tables in native database');
        
        // Users table
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
        error_log('[Migration] Created table: mark_users');
        
        // Permissions table
        $connection->execute('CREATE TABLE IF NOT EXISTS "permissions" (
            "id" INTEGER PRIMARY KEY AUTOINCREMENT,
            "key" TEXT NOT NULL,
            "label" TEXT NOT NULL,
            "group" TEXT NOT NULL,
            "createdAt" TEXT
        )');
        error_log('[Migration] Created table: permissions');
        
        // Sessions table
        $connection->execute('CREATE TABLE IF NOT EXISTS "sessions" (
            "id" TEXT PRIMARY KEY,
            "payload" TEXT NOT NULL,
            "last_activity" INTEGER NOT NULL
        )');
        error_log('[Migration] Created table: sessions');
        
        // Roles table
        $connection->execute('CREATE TABLE IF NOT EXISTS "roles" (
            "id" INTEGER PRIMARY KEY AUTOINCREMENT,
            "name" TEXT NOT NULL,
            "slug" TEXT NOT NULL,
            "description" TEXT,
            "isSuperAdmin" TEXT NOT NULL DEFAULT 0,
            "createdAt" TEXT,
            "updatedAt" TEXT
        )');
        error_log('[Migration] Created table: roles');
        
        // Role permissions junction table
        $connection->execute('CREATE TABLE IF NOT EXISTS "role_permissions" (
            "roleId" INTEGER NOT NULL,
            "permissionId" INTEGER NOT NULL
        )');
        $connection->execute('CREATE UNIQUE INDEX IF NOT EXISTS "idx_role_permissions_unique"
            ON "role_permissions" ("roleId", "permissionId")');
        error_log('[Migration] Created table: role_permissions');
        
        // User roles junction table (mark_roles)
        $connection->execute('CREATE TABLE IF NOT EXISTS "mark_roles" (
            "user_id" INTEGER NOT NULL,
            "role_id" INTEGER NOT NULL,
            PRIMARY KEY ("user_id", "role_id"),
            FOREIGN KEY ("user_id") REFERENCES "mark_users" ("id") ON DELETE CASCADE,
            FOREIGN KEY ("role_id") REFERENCES "roles" ("id") ON DELETE CASCADE
        )');
        $connection->execute('CREATE INDEX IF NOT EXISTS "idx_mark_roles_role_id"
            ON "mark_roles" ("role_id")');
        error_log('[Migration] Created table: mark_roles');
        
        // Migrations table (for tracking which migrations have been run)
        $connection->execute('CREATE TABLE IF NOT EXISTS "migrations" (
            "id" INTEGER PRIMARY KEY AUTOINCREMENT,
            "migration" TEXT NOT NULL,
            "batch" INTEGER NOT NULL
        )');
        error_log('[Migration] Created table: migrations');
        
        // Notifications table
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
        error_log('[Migration] Created table: notifications');
        
        // Portfolio items table
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
        error_log('[Migration] Created table: portfolio_items');
        
        error_log('[Migration] System schema created successfully');
    }

    public function down(ConnectionInterface $connection): void
    {
        error_log('[Migration] Dropping system tables');
        
        $connection->execute('DROP TABLE IF EXISTS "rate_limits"');
        $connection->execute('DROP TABLE IF EXISTS "portfolio_items"');
        $connection->execute('DROP TABLE IF EXISTS "notifications"');
        $connection->execute('DROP TABLE IF EXISTS "migrations"');
        $connection->execute('DROP TABLE IF EXISTS "mark_roles"');
        $connection->execute('DROP TABLE IF EXISTS "role_permissions"');
        $connection->execute('DROP TABLE IF EXISTS "roles"');
        $connection->execute('DROP TABLE IF EXISTS "sessions"');
        $connection->execute('DROP TABLE IF EXISTS "permissions"');
        $connection->execute('DROP TABLE IF EXISTS "mark_users"');
        
        error_log('[Migration] System tables dropped');
    }
};
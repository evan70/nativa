-- ======================================================
-- Init Script: cardboard.db
-- Covers: mark (users, roles, permissions), cardboard (settings, audit), sessions, notifications
-- ======================================================

-- Drop all tables in reverse dependency order
DROP TABLE IF EXISTS "cardboard_audit_log";
DROP TABLE IF EXISTS "cardboard_settings";
DROP TABLE IF EXISTS "notifications";
DROP TABLE IF EXISTS "sessions";
DROP TABLE IF EXISTS "migrations";
DROP TABLE IF EXISTS "mark_roles";
DROP TABLE IF EXISTS "role_permissions";
DROP TABLE IF EXISTS "roles";
DROP TABLE IF EXISTS "permissions";
DROP TABLE IF EXISTS "mark_users";

-- ======================================================
-- System: mark_users
-- ======================================================
CREATE TABLE "mark_users" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "email" TEXT NOT NULL,
    "password" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "rememberToken" TEXT,
    "isActive" TEXT NOT NULL DEFAULT '1',
    "createdAt" TEXT,
    "updatedAt" TEXT
);

-- ======================================================
-- System: permissions
-- ======================================================
CREATE TABLE "permissions" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "key" TEXT NOT NULL,
    "label" TEXT NOT NULL,
    "group" TEXT NOT NULL,
    "createdAt" TEXT
);

-- ======================================================
-- System: roles
-- ======================================================
CREATE TABLE "roles" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "name" TEXT NOT NULL,
    "slug" TEXT NOT NULL,
    "description" TEXT,
    "isSuperAdmin" TEXT NOT NULL DEFAULT '0',
    "createdAt" TEXT,
    "updatedAt" TEXT
);

-- ======================================================
-- System: role_permissions (junction)
-- ======================================================
CREATE TABLE "role_permissions" (
    "roleId" INTEGER NOT NULL,
    "permissionId" INTEGER NOT NULL,
    FOREIGN KEY ("roleId") REFERENCES "roles" ("id") ON DELETE CASCADE,
    FOREIGN KEY ("permissionId") REFERENCES "permissions" ("id") ON DELETE CASCADE
);
CREATE UNIQUE INDEX IF NOT EXISTS "idx_role_permissions_unique"
    ON "role_permissions" ("roleId", "permissionId");

-- ======================================================
-- System: mark_roles (user-role junction)
-- ======================================================
CREATE TABLE "mark_roles" (
    "user_id" INTEGER NOT NULL,
    "role_id" INTEGER NOT NULL,
    PRIMARY KEY ("user_id", "role_id"),
    FOREIGN KEY ("user_id") REFERENCES "mark_users" ("id") ON DELETE CASCADE,
    FOREIGN KEY ("role_id") REFERENCES "roles" ("id") ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS "idx_mark_roles_role_id"
    ON "mark_roles" ("role_id");

-- ======================================================
-- System: sessions
-- ======================================================
CREATE TABLE "sessions" (
    "id" TEXT PRIMARY KEY,
    "payload" TEXT NOT NULL,
    "last_activity" INTEGER NOT NULL
);

-- ======================================================
-- System: migrations (tracking)
-- ======================================================
CREATE TABLE "migrations" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "migration" TEXT NOT NULL,
    "batch" INTEGER NOT NULL
);

-- ======================================================
-- System: notifications
-- ======================================================
CREATE TABLE "notifications" (
    "id" TEXT NOT NULL DEFAULT '',
    "type" TEXT NOT NULL DEFAULT '',
    "notifiableType" TEXT NOT NULL DEFAULT '',
    "notifiableId" TEXT NOT NULL DEFAULT '',
    "data" TEXT NOT NULL DEFAULT '',
    "readAt" TEXT,
    "createdAt" TEXT NOT NULL DEFAULT '',
    PRIMARY KEY ("id")
);

-- ======================================================
-- Cardboard: settings
-- ======================================================
CREATE TABLE "cardboard_settings" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "key" TEXT NOT NULL UNIQUE,
    "value" TEXT NOT NULL DEFAULT '',
    "type" TEXT NOT NULL DEFAULT 'string',
    "group" TEXT NOT NULL DEFAULT 'general',
    "createdAt" TEXT,
    "updatedAt" TEXT
);

-- ======================================================
-- Cardboard: audit log
-- ======================================================
CREATE TABLE "cardboard_audit_log" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "userId" INTEGER,
    "action" TEXT NOT NULL,
    "entityType" TEXT NOT NULL DEFAULT '',
    "entityId" INTEGER,
    "details" TEXT NOT NULL DEFAULT '{}',
    "ipAddress" TEXT,
    "createdAt" TEXT
);
CREATE INDEX IF NOT EXISTS "idx_cardboard_audit_log_userId"
    ON "cardboard_audit_log" ("userId");
CREATE INDEX IF NOT EXISTS "idx_cardboard_audit_log_action"
    ON "cardboard_audit_log" ("action");

-- ======================================================
-- Auth: password_resets
-- ======================================================
CREATE TABLE "password_resets" (
    "email" TEXT NOT NULL,
    "token" TEXT NOT NULL,
    "createdAt" TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS "idx_password_resets_email" ON "password_resets" ("email");
CREATE INDEX IF NOT EXISTS "idx_password_resets_token" ON "password_resets" ("token");

-- === SCHEMA END ===

-- ======================================================
-- Seed data
-- ======================================================

-- Admin role
INSERT INTO "roles" ("name", "slug", "description", "isSuperAdmin", "createdAt", "updatedAt")
VALUES ('Administrator', 'admin', 'Super administrator with full access', '1', datetime('now'), datetime('now'));

-- Default user role
INSERT INTO "roles" ("name", "slug", "description", "isSuperAdmin", "createdAt", "updatedAt")
VALUES ('User', 'user', 'Default authenticated user', '0', datetime('now'), datetime('now'));

-- Admin user (email: admin@marko.local, password: "password")
INSERT INTO "mark_users" ("email", "password", "name", "rememberToken", "isActive", "createdAt", "updatedAt")
VALUES ('admin@marko.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', NULL, '1', datetime('now'), datetime('now'));

-- Assign admin role to admin user
INSERT INTO "mark_roles" ("user_id", "role_id")
VALUES (1, 1);

-- Default cardboard settings
INSERT INTO "cardboard_settings" ("key", "value", "type", "group", "createdAt", "updatedAt")
VALUES ('app_name', 'Cardboard', 'string', 'general', datetime('now'), datetime('now'));
INSERT INTO "cardboard_settings" ("key", "value", "type", "group", "createdAt", "updatedAt")
VALUES ('app_description', 'Marko Framework Application', 'string', 'general', datetime('now'), datetime('now'));
INSERT INTO "cardboard_settings" ("key", "value", "type", "group", "createdAt", "updatedAt")
VALUES ('items_per_page', '10', 'number', 'general', datetime('now'), datetime('now'));
INSERT INTO "cardboard_settings" ("key", "value", "type", "group", "createdAt", "updatedAt")
VALUES ('theme', 'light', 'string', 'appearance', datetime('now'), datetime('now'));
INSERT INTO "cardboard_settings" ("key", "value", "type", "group", "createdAt", "updatedAt")
VALUES ('language', 'en', 'string', 'regional', datetime('now'), datetime('now'));

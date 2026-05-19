-- ======================================================
-- Init Script: portfolio.db
-- Covers: portfolio module (portfolio_items)
-- ======================================================

-- Drop all tables
DROP TABLE IF EXISTS "portfolio_items";

-- ======================================================
-- Portfolio: portfolio_items
-- ======================================================
CREATE TABLE "portfolio_items" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "title" TEXT NOT NULL DEFAULT '',
    "slug" TEXT NOT NULL UNIQUE,
    "subtitle" TEXT NOT NULL DEFAULT '',
    "description" TEXT NOT NULL DEFAULT '',
    "category" TEXT NOT NULL DEFAULT '',
    "role" TEXT NOT NULL DEFAULT '',
    "year" TEXT NOT NULL DEFAULT '',
    "stack" TEXT NOT NULL DEFAULT '',
    "image" TEXT NOT NULL DEFAULT '',
    "display_order" INTEGER DEFAULT 0
);

-- === SCHEMA END ===

-- ======================================================
-- Seed data
-- ======================================================

-- Sample portfolio item
INSERT INTO "portfolio_items" ("title", "slug", "subtitle", "description", "category", "role", "year", "stack", "image", "display_order")
VALUES ('Sample Project', 'sample-project', 'A sample project', 'This is a sample portfolio item.', 'Web Development', 'Full Stack', '2026', 'PHP, JavaScript, SQLite', '', 1);

-- ======================================================
-- Init Script: articles.db
-- Covers: blog module (articles, categories, tags)
-- ======================================================

-- Drop all tables
DROP TABLE IF EXISTS "article_tags";
DROP TABLE IF EXISTS "tags";
DROP TABLE IF EXISTS "categories";
DROP TABLE IF EXISTS "articles";

-- ======================================================
-- Blog: articles
-- ======================================================
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

-- ======================================================
-- Blog: FTS4 full-text search index on articles
-- ======================================================
DROP TABLE IF EXISTS "articles_fts";
CREATE VIRTUAL TABLE "articles_fts" USING fts4(
    title,
    excerpt,
    content
);

-- ======================================================
-- Blog: categories
-- ======================================================
CREATE TABLE "categories" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "name" TEXT NOT NULL,
    "slug" TEXT NOT NULL UNIQUE,
    "description" TEXT
);

-- ======================================================
-- Blog: tags
-- ======================================================
CREATE TABLE "tags" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "name" TEXT NOT NULL,
    "slug" TEXT NOT NULL UNIQUE
);

-- ======================================================
-- Blog: article_tags (junction)
-- ======================================================
CREATE TABLE "article_tags" (
    "article_id" INTEGER,
    "tag_id" INTEGER,
    PRIMARY KEY ("article_id", "tag_id")
);

-- === SCHEMA END ===

-- ======================================================
-- Seed data
-- ======================================================

-- Sample category
INSERT INTO "categories" ("name", "slug", "description")
VALUES ('Getting Started', 'getting-started', 'Articles about getting started with the framework');

-- Sample tag
INSERT INTO "tags" ("name", "slug")
VALUES ('PHP', 'php');

-- Populate FTS index for seed data
INSERT INTO articles_fts(docid, title, excerpt, content)
SELECT id, title, excerpt, content FROM articles;

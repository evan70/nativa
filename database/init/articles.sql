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

-- Sample categories
INSERT INTO "categories" ("name", "slug", "description")
VALUES ('Getting Started', 'getting-started', 'Articles about getting started with the framework');
INSERT INTO "categories" ("name", "slug", "description")
VALUES ('Architecture', 'architecture', 'System design and architecture patterns');
INSERT INTO "categories" ("name", "slug", "description")
VALUES ('Frontend', 'frontend', 'UI, CSS, and frontend development');
INSERT INTO "categories" ("name", "slug", "description")
VALUES ('Backend', 'backend', 'Server-side development and APIs');

-- Sample tags
INSERT INTO "tags" ("name", "slug")
VALUES ('PHP', 'php');
INSERT INTO "tags" ("name", "slug")
VALUES ('Architecture', 'architecture');
INSERT INTO "tags" ("name", "slug")
VALUES ('CSS', 'css');
INSERT INTO "tags" ("name", "slug")
VALUES ('Svelte', 'svelte');
INSERT INTO "tags" ("name", "slug")
VALUES ('TypeScript', 'typescript');

-- Seed articles (with category_id references)
INSERT INTO "articles" ("title", "slug", "excerpt", "content", "image", "status", "published", "category_id", "created_at")
VALUES (
    'Welcome to PHP CMS',
    'welcome-to-php-cms',
    'Your first article',
    'This is your first article. Start creating content! The PHP CMS platform is built with Marko Framework, providing a modular and extensible foundation for building modern web applications. With full-text search support, you can easily find any content across the system.',
    '/dist/assets/images/afe59aa58f41fc48817094cfe7519d0b.webp',
    'published',
    '1',
    1,
    datetime('now', '-2 days')
);

INSERT INTO "articles" ("title", "slug", "excerpt", "content", "image", "status", "published", "category_id", "created_at")
VALUES (
    'Nativa PHP + Svelte 5 Architektúra',
    'nativa-php-svelte-architektura',
    'Moderná DDD architektúra s využitím Svelte 5',
    'Naša architektúra kombinuje PHP 8.4+ s DDD prístupom a Svelte 5 komponentmi. Modularita je kľúčová — každý modul je samostatný balík s vlastnými entitami, repozitármi a kontrolérmi. Frontend beží na Vite so Svelte 5 a TypeScriptom pre rýchle a typovo bezpečné používateľské rozhranie.',
    '/dist/assets/images/26d7d834d1eda62fc868808f37c9b157.webp',
    'published',
    '1',
    2,
    datetime('now', '-1 days')
);

INSERT INTO "articles" ("title", "slug", "excerpt", "content", "image", "status", "published", "category_id", "created_at")
VALUES (
    'BEM + Design Tokens v Praxi',
    'bem-design-tokens-prax',
    'Konzistentný design systém s BEM metódológiou',
    'Implementovali sme design tokens systém s BEM komponentmi pre konzistentný UI/UX. Design tokens centralizujú farby, typografiu a spacing do jedného zdroja pravdy. BEM (Block Element Modifier) zabezpečuje škálovateľné a predvídateľné CSS bez prekrývania selektorov. Kombináciou oboch prístupov docielime konzistentný dizajn naprieč celou aplikáciou.',
    '/dist/assets/images/afe59aa58f41fc48817094cfe7519d0b.webp',
    'published',
    '1',
    3,
    datetime('now')
);

INSERT INTO "articles" ("title", "slug", "excerpt", "content", "image", "status", "published", "category_id", "created_at")
VALUES (
    'Modulárny backend s Marko Framework',
    'modularny-backend-marko',
    'Stavba modulárnych PHP aplikácií s Marko Framework',
    'Marko Framework umožňuje stavať modulárne PHP aplikácie pomocou samostatných balíkov. Každý modul môže mať vlastné databázové spojenie, kontrolér, služby a view-y. Moduly sa registrujú cez module.php súbor a môžu navzájom komunikovať cez kontrakty (interfaces). Tento prístup umožňuje jednoduché testovanie a opätovné použitie kódu naprieč projektmi.',
    '/dist/assets/images/26d7d834d1eda62fc868808f37c9b157.webp',
    'published',
    '1',
    4,
    datetime('now', '+1 hours')
);

-- Link articles with tags
INSERT INTO "article_tags" ("article_id", "tag_id") VALUES (1, 1); -- Welcome → PHP
INSERT INTO "article_tags" ("article_id", "tag_id") VALUES (2, 1); -- Nativa → PHP
INSERT INTO "article_tags" ("article_id", "tag_id") VALUES (2, 2); -- Nativa → Architecture
INSERT INTO "article_tags" ("article_id", "tag_id") VALUES (2, 4); -- Nativa → Svelte
INSERT INTO "article_tags" ("article_id", "tag_id") VALUES (3, 3); -- BEM → CSS
INSERT INTO "article_tags" ("article_id", "tag_id") VALUES (3, 5); -- BEM → TypeScript
INSERT INTO "article_tags" ("article_id", "tag_id") VALUES (4, 1); -- Modular → PHP
INSERT INTO "article_tags" ("article_id", "tag_id") VALUES (4, 2); -- Modular → Architecture

-- Populate FTS index for seed data
INSERT INTO articles_fts(docid, title, excerpt, content)
SELECT id, title, excerpt, content FROM articles;

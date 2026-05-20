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
    "tags" TEXT NOT NULL DEFAULT '',
    "display_order" INTEGER DEFAULT 0
);

-- === SCHEMA END ===

-- ======================================================
-- Seed data
-- ======================================================

INSERT INTO "portfolio_items" ("title", "slug", "subtitle", "description", "category", "role", "year", "stack", "image", "tags", "display_order")
VALUES ('Analytics Dashboard', 'analytics-dashboard', 'Data visualization platform', 'Real-time metrics and insights for enterprise clients. Built with vanilla TypeScript and custom charting components.', 'Dashboard', 'Frontend lead', '2026', 'TypeScript, Charts, API', '/dist/assets/images/26d7d834d1eda62fc868808f37c9b157.webp', 'dashboard, analytics, typescript, data', 10);

INSERT INTO "portfolio_items" ("title", "slug", "subtitle", "description", "category", "role", "year", "stack", "image", "tags", "display_order")
VALUES ('E-Commerce API', 'e-commerce-api', 'Headless commerce backend', 'RESTful API powering a multi-vendor marketplace. Modular architecture with event-driven inventory management.', 'Commerce', 'Backend architecture', '2025', 'PHP, Marko, SQLite', '/dist/assets/images/afe59aa58f41fc48817094cfe7519d0b.webp', 'commerce, api, php, backend', 20);

INSERT INTO "portfolio_items" ("title", "slug", "subtitle", "description", "category", "role", "year", "stack", "image", "tags", "display_order")
VALUES ('CMS Platform', 'cms-platform', 'Content management system', 'Flexible content authoring with block-based editing. Multi-tenant support and role-based access control.', 'CMS', 'Product engineer', '2025', 'React, Node.js, PostgreSQL', '/dist/assets/images/c492faf34ca219cccefbde6eedaf2b6b.webp', 'cms, react, node, content', 30);

INSERT INTO "portfolio_items" ("title", "slug", "subtitle", "description", "category", "role", "year", "stack", "image", "tags", "display_order")
VALUES ('DevOps Toolkit', 'devops-toolkit', 'CI/CD automation suite', 'Automated deployment pipelines with rollback support. GitHub Actions integration and environment monitoring.', 'DevOps', 'Delivery automation', '2026', 'Docker, GitHub Actions, Bash', '/dist/assets/images/d1a18cb5ea2f538c0a8d06e4f6e74264.webp', 'devops, docker, ci-cd, automation', 40);

INSERT INTO "portfolio_items" ("title", "slug", "subtitle", "description", "category", "role", "year", "stack", "image", "tags", "display_order")
VALUES ('Mobile SDK', 'mobile-sdk', 'Cross-platform toolkit', 'Native modules for iOS and Android with a unified JavaScript bridge. Offline-first architecture and sync engine.', 'Mobile', 'Platform tooling', '2026', 'Swift, Kotlin, React Native', '/dist/assets/images/d76d493024744f5142823636a88bb4dd.webp', 'mobile, sdk, swift, kotlin, cross-platform', 50);

INSERT INTO "portfolio_items" ("title", "slug", "subtitle", "description", "category", "role", "year", "stack", "image", "tags", "display_order")
VALUES ('Design System', 'design-system', 'Unified component library', 'Comprehensive design system with 50+ accessible components. Themeable architecture with design tokens and automated visual regression testing.', 'Design', 'Design engineer', '2026', 'Storybook, CSS, Figma API', '/dist/assets/images/26d7d834d1eda62fc868808f37c9b157.webp', 'design, storybook, css, design-tokens', 60);

INSERT INTO "portfolio_items" ("title", "slug", "subtitle", "description", "category", "role", "year", "stack", "image", "tags", "display_order")
VALUES ('Event Platform', 'event-platform', 'Virtual events infrastructure', 'End-to-end platform for virtual conferences with live streaming, chat, ticketing, and analytics. Handled 50k+ concurrent attendees.', 'Platform', 'Tech lead', '2025', 'WebRTC, Go, Redis, K8s', '/dist/assets/images/d76d493024744f5142823636a88bb4dd.webp', 'events, webrtc, go, redis, streaming', 70);

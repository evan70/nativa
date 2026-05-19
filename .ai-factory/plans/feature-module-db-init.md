# Plan: Module Database Init Scripts (Dev Phase)

> Creation date: 2026-05-18
> Branch: (fast mode — no branch)

## Goal

Replace fragmented migration system with one init SQL script per database. **nativa.db → zmazaná**, `cardboard.db` becomes the main DB. Only module-based databases remain.

## Database Architecture

| Database | Tables | Modules |
|----------|--------|---------|
| `cardboard.db` | mark_users, permissions, roles, role_permissions, mark_roles, notifications, sessions, cardboard_settings, cardboard_audit_log | mark, cardboard, nativa |
| `articles.db` | articles, categories, tags, article_tags | blog |
| `portfolio.db` | portfolio_items | portfolio |

**No more `nativa.db`.** Everything that was in nativa.db moves to cardboard.db.

## Current State

**4 databases exist:**
- `nativa.db` — mark_users, permissions, roles, role_permissions, mark_roles, notifications, sessions
- `cardboard.db` — cardboard_settings, cardboard_audit_log
- `articles.db` — articles, categories, tags, article_tags
- `portfolio.db` — portfolio_items

**16 migration files** scattered across:
- `database/migrations/` — 7 files
- `modules/mark/database/migrations/` — 6 files
- `modules/blog/database/migrations/` — 2 files
- `modules/cardboard/database/migrations/` — 1 file

## Tasks

### Phase 1: Create Init SQL Scripts

1. **Create `database/init/cardboard.sql`**
   - All tables from former nativa.db + cardboard.db merged:
   - `mark_users` — id, name, email, password, isActive, createdAt, updatedAt
   - `permissions` — id, key, label, group, groupLabel, createdAt, updatedAt
   - `roles` — id, key, name, createdAt, updatedAt
   - `role_permissions` — roleId, permissionId (composite PK)
   - `mark_roles` — markId, roleId (composite PK)
   - `notifications` — id, type, data, readAt, createdAt
   - `sessions` — id, userId, payload, ipAddress, userAgent, lastActivity, createdAt
   - `cardboard_settings` — id, key, value, type, group, createdAt, updatedAt
   - `cardboard_audit_log` — id, userId, action, entityType, entityId, details, ipAddress, createdAt
   - Seed data: admin user, basic roles/permissions, default settings

2. **Create `database/init/articles.sql`**
   - `articles` — id, title, slug, content, excerpt, status, featuredImage, publishedAt, createdAt, updatedAt
   - `categories` — id, name, slug, createdAt
   - `tags` — id, name, slug, createdAt
   - `article_tags` — articleId, tagId (composite PK)
   - Seed data: sample articles

3. **Create `database/init/portfolio.sql`**
   - `portfolio_items` — id, title, slug, description, imageUrl, technologies, sortOrder, isActive, createdAt, updatedAt
   - Seed data: sample portfolio items

### Phase 2: Update Configs

4. **Update `config/database.php`**
   - Default database: `storage/data/cardboard.db` (namiesto nativa.db)
   - Module map: `'mark' => 'cardboard'` (namiesto 'nativa')
   - Module map: odstrániť `'nativa' => 'nativa'`
   - Module map: odstrániť `'database-modular' => 'database-modular'`

5. **Update any code referencing `nativa.db`**
   - Check bootstrap, build.php, tests

### Phase 3: Create Init Runner

6. **Create `database/InitRunner.php`**
   - `run(string $dbName, bool $seed = true): void`
   - Načíta `database/init/{name}.sql`
   - Spustí DDL + seed queries
   - Dropne všetky tabuľky pred vytvorením (čistý reset pre dev)

7. **Create CLI command `php marko db:init`**
   - `php marko db:init` — init all databases
   - `php marko db:init cardboard` — init specific DB
   - `php marko db:init --seed` — include seed data
   - `php marko db:init --force` — skip confirmation

### Phase 4: Cleanup

8. **Remove old migration files & databases**
   - Delete `database/migrations/` (all files)
   - Delete `modules/*/database/migrations/` (all files)
   - Delete `storage/data/nativa.db`
   - Delete existing `storage/data/cardboard.db` (will be recreated)

9. **Update `build.php`**
   - Odstrániť `passthru('php marko db:migrate ...')`

### Phase 5: Testing

10. **Test init scripts run cleanly**
    - `php marko db:init --force` — all DBs created with correct tables
    - `php marko db:init cardboard` — single DB
    - Verify no errors, all tables present
    - PHPUnit tests still pass
    - Registration flow works (creates user in cardboard.db)

---

## Edge Cases

- `cardboard.db` musí obsahovať sessions tabuľku — session driver už je `session-file` (v composer.lock), ale ak by niekto prepol na databázové sessions, DB ju musí mať
- `mark_users` tabuľka musí byť v cardboard.db — mark modul sa pozerá do "svojej" DB, ktorá je teraz cardboard
- Existujúce testy môžu referencovať nativa.db — treba updatnúť

## Verification

After implementation:
1. `storage/data/nativa.db` neexistuje
2. `storage/data/cardboard.db` má všetky tabuľky (mark_users, permissions, roles, sessions, cardboard_settings, atď.)
3. `storage/data/articles.db` a `portfolio.db` majú svoje tabuľky
4. `php vendor/bin/phpunit` — all tests pass
5. Registration → user uložený do cardboard.db
6. Žiadne staré migračné súbory

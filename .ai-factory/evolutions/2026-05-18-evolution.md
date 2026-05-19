# Evolution: 2026-05-18 — Module-Based Database Architecture

## Intelligence Summary
- Major refactor: nativa.db removed, cardboard.db becomes main system database
- Migration system replaced with SQL init scripts for dev phase
- All module databases are now independent: cardboard, articles, portfolio
- Pattern: one init SQL script per database with `-- === SCHEMA END ===` marker for seed data separation

## Changes Applied

### Database Architecture
- **Before:** 4 databases (nativa.db with system tables, cardboard.db with settings, articles.db, portfolio.db) + 16 migration files across 4 directories
- **After:** 3 module-based databases with SQL init scripts:
  - `cardboard.db` — system tables (users, roles, permissions, sessions, notifications) + cardboard tables (settings, audit)
  - `articles.db` — blog tables (articles, categories, tags)
  - `portfolio.db` — portfolio items
- Removed `database/migrations/`, `modules/*/database/migrations/`, `storage/data/nativa.db`

### New Infrastructure
- `database/init/` — SQL init scripts with seed data marker
- `database/init.php` — CLI entry point
- `modules/controllers/src/Database/InitRunner.php` — runner class (PDO-based)
- `modules/controllers/src/Database/CardboardConnection.php` — replaces NativaConnection
- Makefile targets: `make db-init`, `make db-reset`, `make db-init-cardboard`

### Patterns Identified
- `#module-db-init`: SQL init scripts are better than migrations for dev phase — simpler, clearer, no tracking needed
- `#seed-marker`: Use `-- === SCHEMA END ===` comment to separate schema DDL from seed DML in SQL files
- `#db-connections`: Each module gets its own connection via ModuleDatabaseResolver — no shared state between DBs
- `#autoloading-paths`: Path calculation from deep namespace (App\Database\InitRunner in modules/controllers/src/) requires `dirname(__DIR__, 4)` to reach project root

## Skills Improvements
- Consider adding a `#db-architecture` section to `marko-framework` skill covering module-based DB patterns
- InitRunner seed marker pattern could be useful in future projects

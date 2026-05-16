# Dev & Prod Circuits

Nativa uses two distinct **circuits** (operation modes) to separate development from production.

## Overview

| Aspect | Dev Circuit | Prod Circuit |
|---|---|---|
| Purpose | Local development | Deployment artifact |
| Dependencies | `composer install` (full) | `php build.php` (vendorless) |
| `vendor/` | Yes | No |
| `composer.json` / `composer.lock` | Yes | No |
| Test files | Yes | **No — stripped** |
| Dev tools (PHPStan, PHPUnit) | Yes | No |
| Frontend assets | Source (HMR) | Built (manifest) |
| Entry point | `php -S localhost:9000 -t public` | `dist/` deployed to server |


---

## Architecture: `packages/` vs `modules/`

This distinction is critical to how Nativa works:

| Directory | Contents | Ownership | Updates |
|-----------|----------|-----------|---------|
| `packages/` | **Marko Framework core** — runtime engine, routing, database, view, CLI, admin, etc. | Upstream (Marko) | `composer update` — keep on latest |
| `modules/` | **Nativa custom code** — blog, mark (auth + admin UI), htmx, database drivers, etc. | **Ours** — all rewrites and new features go here |

### Rules

1. **All Nativa code** — every rewrite, every new feature, every customisation — goes into `modules/`.
2. **`packages/`** are treated as upstream framework code. They are updated via `composer update` to stay on latest.
3. Never modify `packages/` directly. If framework behaviour needs to change, extend or override it from a module.
4. The `app/` directory is for application-specific glue code (controllers, config, seeders).

### Dev circuit — keeping packages fresh

In the dev circuit, all packages should be kept up-to-date using `make update`:

```bash
make update
```

This does three things:
1. **`sync-marko`** — pulls the latest Marko framework packages from upstream (`marko-php/marko`, tag 0.6.0) into `packages/`
2. **`composer update`** — updates PHP dependencies (Symfony, Pest, PHPStan, etc.)
3. **`pnpm update`** — updates frontend dependencies

To sync only Marko packages (without touching PHP/frontend deps):

```bash
make sync-marko
```

You can also target a different tag:

```bash
make sync-marko SYNC_TAG=0.5.0
```

Packages in `packages/` that don't exist upstream (e.g. `packages/psr`) are skipped and left untouched.

### Prod circuit — pinned versions

The `composer.lock` file (committed to git) ensures the build is reproducible. The CI build job uses `--no-dev` and `--prefer-dist` to install exactly the versions in `composer.lock`. The `build.php` then strips out vendor and generates a runtime manifest from packages/.`
---

## Dev Circuit

The dev circuit is the default working mode. It includes everything needed for local development.

### Prerequisites

- PHP 8.5+
- Composer
- pnpm (for frontend)

### Setup

First run (or when you want latest packages):

```bash
# Update all packages to latest (Marko framework, frontend)
make update
```

Subsequent runs (reproducible from lock file):

```bash
make install
```

The difference:
- **`make update`** — runs `composer update` + `pnpm update`, pulls latest Marko packages and frontend deps
- **`make install`** — runs `composer install` + `pnpm install`, installs exactly what's in the lock file

```bash
# Optional: create .env from template
cp .env.example .env
```

### Available Commands

| Command | Purpose |
|---|---|
| `composer test` | Run Pest tests |
| `composer analyse` | Run PHPStan static analysis |
| `composer check` | Validate + analyse + test |
| `composer serve` | Start PHP dev server on port 9000 |
| `cd templates && pnpm dev` | Start frontend HMR dev server |
| `php marko db:migrate` | Run database migrations |
| `php marko db:seed` | Seed database |

### Running

```bash
# Terminal 1 — PHP backend
php -S localhost:9000 -t public

# Terminal 2 — frontend HMR (optional)
cd templates && pnpm run dev
```

### Docker Dev

```bash
docker compose up
```

---

## Prod Circuit

The prod circuit produces a **thin deployment artifact** — no vendor, no composer.json, no test files, no dev tooling.

### Build

```bash
php build.php
```

This creates a production build in `./dist/` with:
- Application source code (`app/`, `modules/`, `packages/`, `bootstrap/`)
- Configuration (`config/`)
- Database files (`database/`, `storage/data/`)
- Built frontend assets (`templates/` with built assets only)
- Production autoloader (no vendor dependency)
- Runtime manifest (replaces per-package composer.json)

Without in `dist/`:
- ❌ No `vendor/` directory
- ❌ No `composer.json` / `composer.lock` (root or per-package)
- ❌ No test files (`**/tests/`, `**/*.test.ts`)
- ❌ No dev configs (`phpunit.xml`, `phpstan.neon`, `.phpunit.cache/`)
- ❌ No raw source maps or `.ts` sources
- ❌ No `node_modules/`

### Deployment

```bash
# Build locally
php build.php

# Upload to server
rsync -av dist/ user@server:/var/www/nativa/

# On server: set permissions
chmod -R 755 /var/www/nativa/storage
chown -R www-data:www-data /var/www/nativa/storage
```

### Verification

After building, you can verify the artifact is clean:

```bash
# No vendor/
test ! -d dist/vendor

# No composer.json
test ! -f dist/composer.json
test "$(find dist -name 'composer.json' | wc -l)" = 0

# No test files
test "$(find dist -path '*/tests/*' -type f | wc -l)" = 0

# No dev configs
test ! -f dist/phpunit.xml
test ! -f dist/phpstan.neon
```

---

## CI Pipeline

The CI pipeline (`./.github/workflows/ci.yml`) runs both circuits:

1. **Quality job** (dev circuit) — `composer install` (full), PHPStan, Pest tests, composer validate, security audit
2. **Build job** (prod circuit) — `composer install --no-dev`, build frontend, run migrations, `php build.php`, smoke test from `dist/`, upload artifact

The build job explicitly copies `vendor/` back **only for smoke testing** — actual production deployment never includes it.

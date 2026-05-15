# Plan: Dev & Prod Circuits for Nativa

> Creation date: 2026-05-15
> Branch: (fast mode — no branch)

## Goal

Formalize two distinct operation modes (circuits) for the Nativa project:
- **Dev circuit** — full development environment with vendor, dev tools, tests, frontend HMR
- **Prod circuit** — stripped-down production artifact (no vendor, no composer.json, no tests, min thin)

## Settings

- Testing: N/A (plan is about infrastructure, not code)
- Logging: N/A
- Docs: minimal inline docs in Makefile + circuit documentation

## Current State

The project already has:
- `build.php` — generates `dist/` without vendor/composer.json
- `composer.json` with `require-dev` (pest, phpstan)
- CI pipeline that runs quality checks then builds prod dist
- `composer install --no-dev` used in CI build job

**What's missing:**
- `build.php` copies everything including test dirs → prod dist has test files
- No Makefile for common dev tasks
- No Docker Compose for dev
- No documentation of the two circuits
- No automated way to verify prod dist is clean

## Tasks

### Phase 1: Formalize Circuits

1. **Add `CIRCUITS.md` documenting dev and prod circuits**
   - Dev: `composer install`, vendor/, tests, phpstan, phpunit, frontend dev server
   - Prod: `php build.php` → `dist/`, no vendor, no composer.json, no tests, deployable
   - Explicitly describe what each circuit contains and how to switch between them
   - Cover: dependencies, tooling, testing, database, frontend assets

### Phase 2: Dev Circuit Tooling

2. **Create `Makefile` for common dev operations**
   - `make install` — install PHP + frontend deps
   - `make dev` — start PHP dev server + frontend HMR
   - `make test` — run pest tests
   - `make analyse` — run phpstan
   - `make lint` — run composer validate + phpstan
   - `make build` — php build.php (prod artifact)
   - `make clean` — clean vendor, dist, etc.

3. **Create `docker-compose.yml` for dev circuit**
   - PHP 8.5 CLI image with composer
   - Mount source code as volume
   - Expose dev server port
   - Run `make install` on startup
   - No prod Docker (user wants `build.php` for prod)

### Phase 3: Prod Circuit — Max Thin Build

4. **Update `build.php` to strip test directories**
   - Exclude `**/tests/`, `**/Tests/`, `**/*.test.ts`, `**/*.spec.ts` from all copied dirs
   - Exclude `.phpunit.cache/`, `phpunit.xml`, `phpstan.neon`, `.phpstan` from dist
   - Exclude dev-only configs (`phpunit.xml`, `run_test.sh`, `test_server.sh`)
   - Verify prod dist has zero test files

5. **Add `dist/ verification` step to build.php**
   - After build, assert no `vendor/` dir exists in dist
   - Assert no `composer.json` exists in dist
   - Assert no `**/tests/` dirs exist in dist
   - Assert no dev config files exist in dist
   - Exit with error if any dev artifact leaked into prod

### Phase 4: CI Alignment

6. **Update CI workflow to use the same dev/prod patterns**
   - Quality job → dev circuit (composer install, tests, phpstan)
   - Build job → build.php with `MARKO_SKIP_FRONTEND_BUILD=1`
   - Smoke test uses dist/ (already done)
   - Ensure CI build job runs the new verification step

### Phase 5: Documentation

7. **Update `README.md` with circuit badges/summary**
   - Add quick "Dev Circuit" and "Prod Circuit" section
   - Link to CIRCUITS.md
   - Update `AGENTS.md` with new files

## Edge Cases

- **Dev Docker**: permissions for storage/ directory must be writable by www-data inside container
- **Build stripping**: `packages/` contains first-party tests that MUST NOT be in prod — use explicit exclusion list, not just a blanket `tests/` pattern, to avoid false matches
- **Composer.lock**: must exist for reproducible builds in CI; dev circuit uses `--frozen-lockfile`
- **Frontend assets**: prod dist includes built assets from templates/ — build.php should exclude `node_modules/` (already done) and source maps in prod

## Verification

After implementation:
1. `make install && make test && make analyse` → all pass
2. `make build` → `dist/` has:
   - No `vendor/` dir
   - No `composer.json` or `composer.lock`
   - No `tests/` directories anywhere
   - No `phpunit.xml`, `phpstan.neon`, `run_test.sh`, `test_server.sh`
   - No `.phpunit.cache/`
   - Frontend assets built (no `node_modules/`, no raw `.ts` sources)
3. `make dev` (or Docker) → PHP dev server + frontend HMR work
4. CI pipeline passes same checks

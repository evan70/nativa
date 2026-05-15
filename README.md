# Marko Skeleton

Application skeleton for the [Marko Framework](https://marko.build).

## Quick Start

```bash
composer create-project marko/skeleton my-app
cd my-app
cp .env.example .env
composer install
./marko up
```

Visit http://localhost:8000

## Dev & Prod Circuits

Nativa uses two distinct **circuits** for development and production.

| Circuit | Dependencies | Vendor | Tests | Dev Tools |
|---------|-------------|--------|-------|-----------|
| **Dev** | `composer install` | Yes | Yes | PHPStan, Pest |
| **Prod** | `php build.php` → `dist/` | **No** | **No** | **No** |

- **Dev circuit** — full environment with all tooling. Run `make install && make dev`.
- **Prod circuit** — thin deployment artifact via `php build.php`:
  - Strips all **test directories** (`**/tests/`, `**/*.test.ts`) from the build
  - Removes **dev configs** (`phpunit.xml`, `phpstan.neon`, `Makefile`, `docker-compose.yml`)
  - **Verifies** the artifact has no vendor/, no composer.json, no tests — **build fails if anything leaked**

See [Circuits](docs/CIRCUITS.md) for full details.

## Key Features

- **Modular Architecture** — Packages, modules, and app code separation
- **CLI Tools** — Database migrations, queue workers, dev server
- **Build-Based Deployment** — Vendorless production builds
- **SQLite Support** — Configurable to other databases

## Project Structure

| Directory | Purpose |
|-----------|---------|
| `app/` | Your application code |
| `modules/` | Nativa custom code — **all rewrites and features go here** |
| `packages/` | Marko Framework core — upstream, never modify directly |
| `config/` | Configuration |
| `public/` | Web entry point |
| `storage/` | Logs, cache, sessions |
| `docs/` | Documentation |

> **Key rule:** All Nativa code (rewrites, features, customisations) goes into `modules/`.  
> The `packages/` directory is upstream Marko Framework — updated via `composer update`, never modified directly.

---

## Documentation

| Guide | Description |
|-------|-------------|
| [Getting Started](docs/getting-started.md) | Installation, setup, first steps |
| [Dev & Prod Circuits](docs/CIRCUITS.md) | Development and production environments |
| [CLI Reference](docs/cli.md) | All available commands |
| [Deployment](docs/deployment.md) | Production deployment |
| [Security](docs/security.md) | Security policy |
| [Cardboard](docs/marko/src/content/docs/packages/cardboard.md) | TypeScript UI components |

## License

MIT

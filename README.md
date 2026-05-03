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

## Key Features

- **Modular Architecture** — Packages, modules, and app code separation
- **CLI Tools** — Database migrations, queue workers, dev server
- **Build-Based Deployment** — Vendorless production builds
- **SQLite Support** — Configurable to other databases

## Project Structure

| Directory | Purpose |
|-----------|---------|
| `app/` | Your application code |
| `modules/` | Third-party modules |
| `packages/` | Framework packages |
| `config/` | Configuration |
| `public/` | Web entry point |
| `storage/` | Logs, cache, sessions |

---

## Documentation

| Guide | Description |
|-------|-------------|
| [Getting Started](docs/getting-started.md) | Installation, setup, first steps |
| [CLI Reference](docs/cli.md) | All available commands |
| [Deployment](docs/deployment.md) | Production deployment |
| [Security](docs/security.md) | Security policy |

## License

MIT
# Project: Marko Skeleton

## Overview
Application skeleton for the Marko Framework, a PHP framework for building web applications. This project provides a starting point for Marko applications with the standard directory structure and configuration.

## Core Features
- PHP 8.5+ runtime
- Marko Framework core components
- CLI commands for database migrations, seeding, queue work, and more
- Modular structure with `app/` for application code, `modules/` for third-party modules, and `packages/` for packages
- Environment-based configuration
- SQLite database by default (configurable)
- Logging and session management
- View rendering system
- Frontend asset management via templates directory

## Tech Stack
- **Language:** PHP 8.5+
- **Framework:** Marko Framework
- **Database:** SQLite (configurable to other drivers)
- **ORM:** Marko's built-in database layer (supports migrations and seeders)
- **Templating:** Marko's view system (PHP-based templates)
- **Frontend Assets:** Managed via templates directory (uses pnpm for asset building)

## Architecture Notes
The Marko Framework follows a modular architecture where application code resides in the `app/` directory, following PSR-4 autoloading. The framework provides a CLI (`marko`) for common tasks. Configuration is stored in `config/` and environment variables in `.env`. The framework supports modules and packages for extensibility.

## Non-Functional Requirements
- Logging: Configurable via LOG_LEVEL in .env, using file driver by default
- Error handling: Structured error responses via Marko's exception handling

## Architecture
See `.ai-factory/ARCHITECTURE.md` for detailed architecture guidelines.
Pattern: Modular Monolith
This project uses `modules/init` for module grouping and idle eviction:

```bash
# List all modules
php marko module:list

# Show bindings and groups
php marko module:bindings

# Activate a group
php marko module:activate <group>

# Unbind a group
php marko module:unbind <group> [--force]

# Evict idle groups
php marko module:evict [group]
```

**Configuration:** `config/module.php`
- Security: Includes CSRF protection, input validation, and output escaping (as per Marko Framework features)
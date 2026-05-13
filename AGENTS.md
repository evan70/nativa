# AGENTS.md

> Project map for AI agents. Keep this file up-to-date as the project evolves.

## Project Overview
Application skeleton for the Marko Framework, a PHP framework for building web applications. This project provides a starting point for Marko applications with the standard directory structure and configuration.

## Tech Stack
- **Language:** PHP 8.5+
- **Framework:** Marko Framework
- **Database:** SQLite (configurable)
- **ORM:** Marko's built-in database layer
- **Frontend Assets:** Managed via templates directory (uses pnpm for asset building)

## Project Structure
```
.
├── .agents/                  # AI agent skills and configuration
│   └── skills/               # Custom skills for the project
├── .ai-factory/              # AI Factory project setup and documentation
│   └── DESCRIPTION.md        # Project specification and tech stack
├── app/                      # Application code (controllers, models, etc.)
│   ├── Seed/                 # Database seeders
│   └── src/                  # Application source code (PSR-4 autoloaded)
├── bootstrap/                # Application bootstrap files
├── config/                   # Configuration files (database, logging, session, view)
├── database/                 # Database-related files
│   └── migrations/           # Database migrations
├── modules/                  # Third-party modules
│   └── blog/                 # Example blog module
├── packages/                 # Marko framework packages (core components)
│   ├── admin/                # Admin panel package
│   ├── cli/                  # CLI commands package
│   ├── database/             # Database abstraction package
│   ├── routing/              # Routing package
│   └── ...                   # Other framework packages
├── public/                   # Web root (publicly accessible)
│   ├── cardboard-assets/     # Public assets
│   └── index.php             # Web entry point
├── storage/                  # Storage for logs, cache, sessions, data
│   ├── data/                 # SQLite database file
│   └── framework/            # Framework storage (sessions, cache, views)
├── templates/                # Frontend templates and assets
│   ├── app/                  # Application templates
│   ├── blog/                 # Blog templates
│   └── static/               # Static assets
├── tests/                    # Test suites
│   ├── Connection/           # Database connection tests
│   ├── Diff/                 # Diff-related tests
│   └── Introspection/        # Introspection tests
├── vendor/                   # Composer dependencies
├── .env                      # Environment variables (local)
├── .env.example              # Environment template
├── composer.json             # PHP dependencies
├── composer.lock             # Locked dependencies
├── phpunit.xml               # PHPUnit configuration
├── marko                     # Marko CLI executable
├── build.php                 # Build script
├── README.md                 # Project documentation
├── DEPLOYMENT.md             # Deployment instructions
├── SECURITY.md               # Security policy
└── LICENSE                   # License file (MIT)
```

## Key Entry Points
| File | Purpose |
|------|---------|
| `public/index.php` | Web entry point for HTTP requests |
| `bootstrap/app.php` | Application bootstrap and initialization |
| `marko` | CLI executable for framework commands |
| `app/src/HomeController.php` | Example controller (if exists) |

## Documentation
| Document | Path | Description |
|----------|------|-------------|
| README | README.md | Project landing page |
| Getting Started | docs/getting-started.md | Installation, setup, first steps |
| CLI Reference | docs/cli.md | All available commands |
| Deployment | docs/deployment.md | Production deployment |
| Security | docs/security.md | Security policy |
| Cardboard | docs/marko/src/content/docs/packages/cardboard.md | TypeScript UI components |
| Architecture | .ai-factory/ARCHITECTURE.md | Architecture decisions |

## AI Context Files
| File | Purpose |
|------|---------|
| AGENTS.md | This file — project structure map |
| .ai-factory/DESCRIPTION.md | Project specification and tech stack |
| .ai-factory/ARCHITECTURE.md | Architecture decisions and guidelines |
| CLAUDE.md | Agent instructions and preferences (if exists) |
| .mcp.json | MCP server configuration (if exists) |
| templates/RULES.md | Frontend development rules (BEM, events, Vite config) |

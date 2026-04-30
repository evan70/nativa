# Architecture: Modular Monolith

## Overview
The Marko Framework skeleton follows a **Modular Monolith** architecture pattern. This single-deployment unit with strong module boundaries provides the best balance for a PHP web application framework. It offers simple operational complexity while maintaining future extraction readiness if the application needs to scale into microservices later.

This architecture was chosen because:
- PHP applications typically benefit from shared-memory performance
- The Marko Framework provides module conventions out of the box
- Small to medium team sizes (1-15 developers) can work efficiently
- Business logic complexity is moderate for a skeleton project

## Decision Rationale
- **Project type:** Web application skeleton for PHP framework
- **Tech stack:** PHP 8.5+, Marko Framework, SQLite
- **Key factor:** Modular monolith provides the right balance of simplicity and structure for a framework skeleton that developers will extend

## Folder Structure
```
project/
├── app/                      # Application modules (user code)
│   ├── src/                  # Application source (controllers, services)
│   ├── Seed/                 # Database seeders
│   └── composer.json         # Module definition
├── modules/                  # Third-party modules (reusable components)
├── packages/                 # Marko framework packages (core components)
├── config/                   # Configuration files
├── database/                 # Migrations and database-related
│   └── migrations/
├── public/                   # Web root (entry point)
├── storage/                  # Runtime files (logs, cache, sessions)
│   ├── data/                 # SQLite database file
│   └── framework/            # Framework storage
├── templates/                # View templates and frontend assets
├── tests/                    # Test suites
├── bootstrap/                # Application bootstrap
└── vendor/                   # Composer dependencies
```

## Dependency Rules
- ✅ Application code (`app/`) can depend on framework packages and modules
- ✅ Framework packages are self-contained with clear interfaces
- ✅ Modules can be reused across projects
- ❌ Application code should not modify framework packages
- ❌ Modules should not depend on application-specific code

## Module Communication
- Modules expose explicit public API via defined interfaces
- Framework provides dependency injection container for service resolution
- Database migrations are versioned and managed through CLI
- Configuration is loaded from `config/` and environment variables

## Key Principles
1. **Separation of Concerns** — Each directory has a single responsibility
2. **Explicit Dependencies** — Dependencies are declared in composer.json
3. **Framework Conventions** — Follow Marko's project structure patterns
4. **Testability** — Tests in `tests/` with PestPHP, easy to extend

## Code Examples

### Module Definition (app/composer.json)
```json
{
    "name": "app/controllers",
    "type": "library",
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    },
    "extra": {
        "marko": {
            "module": true
        }
    }
}
```

### Controller Pattern
```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Marko\Http\Request;
use Marko\Http\Response;

class HomeController
{
    public function index(Request $request): Response
    {
        return new Response('Hello, Marko!');
    }
}
```

### Configuration Pattern
```php
<?php

declare(strict_types=1);

return [
    'driver' => 'sqlite',
    'database' => dirname(__DIR__) . '/storage/data/database.sqlite',
];
```

## Anti-Patterns
- ❌ **Circular Dependencies** — Avoid modules depending on each other circularly
- ❌ **Framework Modifications** — Don't modify packages/ directly; extend via modules
- ❌ **Business Logic in Config** — Keep config simple; logic belongs in code
- ❌ **Direct Database Access in Controllers** — Use repository pattern or ORM
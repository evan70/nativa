# Architecture: Modular Monolith

## Overview

This project uses a **Modular Monolith** architecture — a single deployment unit with strong module boundaries. The application skeleton provides a starting point for Marko Framework applications with clear separation between framework packages, third-party modules, and application code.

The Modular Monolith pattern is ideal for this project because:
- It's the default recommended architecture for new projects with unclear requirements
- It provides simple deployment (single unit) with clear module boundaries
- It allows future extraction to microservices if needed, without refactoring
- The Marko Framework's existing `packages/` and `modules/` structure already supports this pattern

## Decision Rationale

- **Project type:** Application skeleton for Marko Framework
- **Tech stack:** PHP 8.5+, Marko Framework, SQLite
- **Team size:** Small to medium (skeleton project for teams to build upon)
- **Domain complexity:** Low initially (simple CRUD), grows with application complexity
- **Key factor:** The framework already provides modular structure via `packages/` (framework components) and `modules/` (third-party modules). This architecture extends that pattern to application code.

## Folder Structure

```
.
├── app/                      # Application code (PSR-4 autoloaded)
│   ├── Seed/                 # Database seeders
│   ├── src/                 # Application source code
│   └── module.php           # Application module bindings
├── bootstrap/               # Application bootstrap files
├── config/                  # Configuration files
├── database/
│   └── migrations/          # Database migrations
├── modules/                  # Third-party modules
│   └── blog/                 # Example: blog module
├── packages/                 # Framework packages (core components)
│   ├── admin/                # Admin panel
│   ├── authentication/       # Authentication
│   ├── core/                 # Core framework
│   ├── database/             # Database abstraction
│   ├── routing/              # Routing
│   └── ...                   # Other packages
├── public/                   # Web root
│   └── index.php            # Web entry point
├── storage/                  # Storage for logs, cache, sessions
│   ├── data/                 # SQLite database
│   └── framework/            # Framework storage
└── templates/                # Frontend templates
    ├── app/                  # Application templates
    └── static/               # Static assets
```

## Dependency Rules

The Marko Framework enforces a natural dependency hierarchy. Follow these rules:

- ✅ Application code (`app/src/`) depends on framework packages (`packages/*`)
- ✅ Modules (`modules/*`) depend on framework packages
- ✅ Framework packages depend on Core package only
- ❌ Application code should not reach directly into package internals
- ❌ Third-party modules should not bypass the service container

### Dependency Direction

```
┌─────────────────────────────────────────────┐
│          app/ (Application Code)            │
│         depends on: packages, modules        │
├─────────────────────────────────────────────┤
│         modules/ (Third-Party)               │
│         depends on: packages                  │
├──────────────────────────────────��──────────┤
│         packages/ (Framework)               │
│         depends on: core                      │
├─────────────────────────────────────────────┤
│         packages/core (Core)                 │
│         depends on: nothing                   │
└─────────────────────────────────────────────┘
```

## Module Communication

### Within the Application

Use the Marko service container for dependency injection:

```php
// In app/src/Services/AppService.php
<?php

namespace App\Services;

use Marko\Contracts\LoggerInterface;

class AppService
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function doSomething(): void
    {
        $this->logger->info('Doing something in the application');
    }
}
```

### Between Modules

Modules communicate through **explicit public APIs** — never reach into module internals:

```php
// Using the blog module
<?php

use Modules\Blog\Contracts\PostRepository;

class PostController extends Controller
{
    public function __construct(
        private PostRepository $posts
    ) {}

    public function index(): Response
    {
        $posts = $this->posts->findAll();
        return $this->render('blog.index', ['posts' => $posts]);
    }
}
```

### Using Packages

Access functionality through service bindings defined in package module files:

```php
// In packages/routing/module.php
return new class implements \Marko\ModuleInterface {
    public function register(\Marko\Container $container): void
    {
        $container->singleton(RouterInterface::class, Router::class);
        $container->singleton(RouteCollector::class, RouteCollector::class);
    }
};
```

## Key Principles

### 1. Single Entry Point

All HTTP requests enter through `public/index.php`. The application bootstrap (`bootstrap/app.php`) initializes the container and resolves the router:

```php
// public/index.php
<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$app->handleRequest();
```

### 2. Module Boundaries

Each package and module should expose a clear public API through its `module.php`:

```php
// packages/authentication/module.php
<?php

return new class implements \Marko\ModuleInterface {
    public function register(\Marko\Container $container): void
    {
        // Define what this module exposes
        $container->singleton(AuthInterface::class, Authentication::class);
        $container->singleton(GuardInterface::class, Guard::class);
    }

    public function boot(): void
    {
        // Optional: run after all modules are registered
    }
};
```

### 3. Configuration over Convention

Use `config/` for configuration. Environment-specific settings go in `.env`:

```php
// config/database.php
<?php

return [
    'driver' => env('DB_DRIVER', 'sqlite'),
    'database' => env('DB_DATABASE', storage_path('data/database.sqlite')),
];
```

### 4. Convention for Application Code

Follow PSR-4 autoloading in `app/src/`. Use descriptive names:

```
app/
├── src/
│   ├── Controllers/     # HTTP controllers
│   ├── Models/          # Domain models
│   ├── Services/       # Application services
│   ├── Middleware/      # HTTP middleware
│   └── Exceptions/     # Custom exceptions
```

## Code Examples

### Application Controller

```php
<?php

// app/src/Controllers/HomeController.php
namespace App\Controllers;

use Marko\Http\Controller;
use Marko\Http\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        return $this->render('home.index', [
            'title' => 'Welcome to Marko'
        ]);
    }
}
```

### Service Registration in Module

```php
<?php

// app/module.php
<?php

return new class implements \Marko\ModuleInterface {
    public function register(\Marko\Container $container): void
    {
        // Register application services
        $container->singleton(\App\Services\UserService::class);
        
        // Bind contracts to implementations
        $container->singleton(
            \App\Contracts\UserRepositoryInterface::class,
            \App\Services\UserService::class
        );
    }
};
```

### Using Environment Configuration

```php
<?php

// In any service that needs configuration
$logLevel = env('LOG_LEVEL', 'info');
$debug = env('APP_DEBUG', false);
```

## Anti-Patterns

- ❌ **Don't bypass the service container** — Always use dependency injection instead of `new SomeClass()`
- ❌ **Don't hardcode configuration** — Use `config/` files and `env()` for environment-specific values
- ❌ **Don't reach into module internals** — Use only the public API exposed in module files
- ❌ **Don't mix application code in packages/** — Keep application code in `app/src/`, packages in `packages/`
- ❌ **Don't skip migrations for schema changes** — Always use `php marko migrate` for database changes